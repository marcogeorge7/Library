<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Series;
use Illuminate\Support\Str;

class BarCode
{
    public static function generate(Edition $edition, $bookCode): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Get copy details
        $book = $edition->book;
        $categoryId = $book->category->id;

        // Generate category prefix based on category ID
        $catPrefix = $catPrefix = (int) ($categoryId / 26).$alphabet[($categoryId % 26) - 1];

        if (Str::length($bookCode) === 4) {
            $series = Series::create([
                'name' => $edition->book->name,
            ]);
        } else {
            $series = Series::latest()->first();
        }
        $edition->book()->update([
            'series_id' => $series->id,
        ]);

        // Generate series prefix based on series ID
        $seriesLetter = Str::substr($bookCode, 5, 1);
        $bookNumber = Str::padLeft($book->id, 5, '0');

        // Create book barcode
        return $catPrefix.$seriesLetter.$bookNumber.str_pad($edition->partCode, 2, '0', STR_PAD_LEFT);

    }
}
