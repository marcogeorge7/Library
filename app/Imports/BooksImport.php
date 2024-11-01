<?php

namespace App\Imports;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Publisher;
use App\Models\Translator;
use App\Services\BarCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;

class BooksImport implements ToCollection
{
    use Importable;

    public function collection(Collection $collection)
    {
        $headings = $collection[0];
        // book name / number of copies / publisher name / category name / translator name / author name
        $collection->forget(0);
        foreach ($collection as $row) {
            $category = Category::where('name', 'like', "%$row[5]%")->first();
            $publisher = Publisher::where('name', 'like', "%{$row[4]}%")->first();
            if ($row[4] != null && ! $publisher) {
                $publisher = Publisher::create([
                    'name' => $row[4],
                ]);
            }
            $author = Author::where('name', 'like', "%{$row[6]}%")->first();
            if ($row[7] != null && ! $author) {
                $author = Author::create([
                    'name' => $row[7],
                ]);
            }
            $translator = Translator::where('name', 'like', "%{$row[6]}%")->first();
            if ($row[6] != null && ! $translator) {
                $translator = Translator::create([
                    'name' => $row[6],
                ]);
            }
            $partCode = 1;

            if (preg_match('/ج(\d+)\s(?!-)/u', $row[1], $matches)) {
                $partCode = $matches[1] ?? 1;
            }

            $book = Book::create([
                'name' => $row[1],
                'revisor_id' => null,
                'category_id' => $category->id,
                'series_id' => null,
            ]);

            $book->author()->attach($author);

            $copies = $row[3];

            $edition = Edition::create([
                'book_id' => $book->id,
                'publisher_id' => $publisher->id,
                'partCode' => $partCode,
                'publish_year' => null,
            ]);

            for ($i = 0; $i < $copies; $i++) {
                $copy = Copy::create([
                    'edition_id' => $edition->id,
                    'barcode' => BarCode::generate($edition),
                ]);
            }
        }
    }
}
