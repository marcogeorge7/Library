<?php

namespace App\Imports;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Publisher;
use App\Models\Series;
use App\Models\Translator;
use App\Services\BarCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BooksImport implements ToCollection,WithChunkReading
{
    use Importable;

    private const SERIES_CODE_LENGTH = 5;

    private const PARENT_CODE_LENGTH = 4;

    public function collection(Collection $collection)
    {
        $collection->forget(0);
        $this->importBooks($collection);
    }

    private function importBooks(Collection $books)
    {
        $books->each(function ($row, $key) use ($books) {
            if ($key === 8163) {
                exit;
            }
            $category = $this->findOrCreateCategory($row[5]);

            $publisher = $this->findOrCreatePublisher($row[4]);

            $author = $this->findOrCreateAuthor($row[7]);

            $translator = Translator::where('name', $row[6])->first();

            $partCode = $this->extractPartCode($row[1]);
            $book = $this->createOrUpdateBook($row, $category, $author);

            $this->handleSeriesLogic($book, $row, $key, $books);

            $edition = $this->createEdition($book, $publisher, $partCode, $translator, $row[3]);

            if ($translator) {
                $edition->translators()->attach($translator);
            }

            $this->createCopies($edition, $row[2], $row[3], $book);
        });
    }

    private function findOrCreateCategory($name)
    {
        return Category::where('name', 'like', "%$name%")->first();
    }

    private function findOrCreatePublisher($name)
    {
        if (! $name) {
            return null;
        }

        return Publisher::firstOrCreate(['name' => $name]);
    }

    private function findOrCreateAuthor($name)
    {
        if (! $name) {
            return null;
        }

        return Author::firstOrCreate(['name' => $name]);
    }

    private function extractPartCode($bookName)
    {
        preg_match('/\bج(\d+)\b(?!\s*-)/u', $bookName, $matches);

        return $matches[1] ?? 1;
    }

    private function createOrUpdateBook($row, $category, $author)
    {
        $book = Book::where('name', $row[1])->first();

        if (! $book || $book->author()->where('author_id', $author->id)->doesntExist()) {
            $book = Book::create([
                'name' => $row[1],
                'category_id' => $category->id,
                'old_code' => $row[0],
            ]);
            $book->author()->attach($author);
        }

        return $book;
    }

    private function handleSeriesLogic($book, $row, $key, $books)
    {
        $nextBookCode = $key != 3999 ? $books[$key + 1][0] : $row[0];
        $currentCodeLength = Str::length($row[0]);

        if ($currentCodeLength === self::SERIES_CODE_LENGTH) {
            $seriesId = Series::orderByDesc('id')->first()->id;
            $book->update([
                'series_id' => $seriesId,
                'order' => Book::where('series_id', $seriesId)->first()->order,
            ]);
        } else {
            $book->update(['order' => $book->getLastBookOrderInCategory()]);
        }

        if ($currentCodeLength === self::PARENT_CODE_LENGTH && Str::length($nextBookCode) === self::SERIES_CODE_LENGTH) {
            $series = Series::create(['name' => $row[1]]);
            $book->update(['series_id' => $series->id]);
        }
    }

    private function createEdition($book, $publisher, $partCode, $translator, $internalBorrowing)
    {
        return Edition::create([
            'book_id' => $book->id,
            'publisher_id' => $publisher->id,
            'partCode' => $partCode,
            'lang' => $translator ? 'en' : 'ar',
            'internal_borrowing' => $internalBorrowing === 'YES',
        ]);
    }

    private function createCopies($edition, $copiesCount, $internalBorrowing, $book)
    {
        $bookBarCode = BarCode::generate($edition);

        if ((int) $copiesCount > 0) {
            for ($i = 1; $i <= (int) $copiesCount; $i++) {
                $copy = Copy::create([
                    'edition_id' => $edition->id,
                    'barcode' => "$bookBarCode$i",
                    'is_borrowed' => false,
                ]);
                Log::info("Copy created: $copy->barcode for book [Book-$book->id] of old code [$book->old_code] , Series = [$book->series_id]");
            }
        } else {
            Copy::create([
                'edition_id' => $edition->id,
                'barcode' => $bookBarCode.'1',
                'internal_borrowing' => $internalBorrowing === 'YES',
                'is_borrowed' => true,
            ]);
            Log::info("Copy Borrowed created: $bookBarCode for book [Book-$book->id] of [$book->old_code] , Series = [$book->series_id]");
        }
    }

    //    public function chunkSize(): int
    //    {
    //        return 4000;
    //    }
    public function chunkSize(): int
    {
        return 8160;
    }
}
