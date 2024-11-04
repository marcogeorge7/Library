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

class BooksImport implements ToCollection, WithChunkReading
{
    use Importable;

    public function collection(Collection $collection)
    {
        $collection->forget(0);

        $this->importBooks($collection);
    }

    private function importBooks(Collection $books)
    {
        foreach ($books as $key => $row) {
            $category = Category::where('name', 'like', "%$row[5]%")->first();
            $publisher = Publisher::where('name', 'like', "%{$row[4]}%")->first();
            if ($row[4] != null && ! $publisher) {
                $publisher = Publisher::create([
                    'name' => $row[4],
                ]);
            }
            $author = Author::where('name', 'like', "%{$row[7]}%")->first();
            if ($row[7] != null && ! $author) {
                $author = Author::create([
                    'name' => $row[7],
                ]);
            }

            $partCode = 1;

            if (preg_match('/\bج(\d+)\b(?!\s*-)/u', $row[1], $matches)) {
                $partCode = $matches[1];
            }

            $book = Book::where('name', $row[1])->first();
            if (! $book || $book->author()->where('author_id', $author->id)->doesntExist()) {
                $book = Book::create([
                    'name' => $row[1],
                    'revisor_id' => null,
                    'category_id' => $category->id,
                    'series_id' => null,
                ]);
            }

            $book->author()->attach($author);

            $nextBook = $key != 999 ? $books[$key + 1] : $row[0];
            $nextBookCode = Str::substr($nextBook[0], 0, 4);
            $currentBookCode = Str::substr($row[0], 0, 4);

            $series = Series::latest()->first();

            if ($currentBookCode !== $nextBookCode || ! $series) {
                $series = Series::create([
                    'name' => $book->name,
                ]);
            }
            $book->update([
                'series_id' => $series->id,
            ]);

            $translator = Translator::where('name', $row[6])->first();

            $edition = Edition::create([
                'book_id' => $book->id,
                'publisher_id' => $publisher->id,
                'partCode' => $partCode,
                'publish_year' => null,
                'lang' => $translator ? 'en' : 'ar',
                'internal_borrowing' => $row[3] === 'YES',

            ]);

            if ($row[6] != null && $translator) {
                $edition->translators()->attach($translator);
            }

            $bookCode = $row[0];
            $bookBarCode = BarCode::generate($edition, $bookCode);

            $copies = (int) $row[2];
            if ($copies != 0) {

                for ($i = 1; $i <= $copies; $i++) {
                    $copy = Copy::create([
                        'edition_id' => $edition->id,
                        'barcode' => "$bookBarCode$i",
                        'is_borrowed' => false,
                    ]);

                    Log::info("Copy created: $copy->barcode for book [Book-$book->id]");
                }
            } else {
                Copy::create([
                    'edition_id' => $edition->id,
                    'barcode' => $bookBarCode.'1',
                    'internal_borrowing' => $row[3] === 'YES',
                    'is_borrowed' => true,
                ]);
                Log::info('Copy Borrowed created: '.$bookBarCode.'1 for book [Book-'.$book->id.']');
            }

        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
