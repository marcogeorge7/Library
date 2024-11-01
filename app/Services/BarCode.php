<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Edition;

class BarCode
{
    public static function generate(Edition $edition): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Get copy details
        $book = $edition->book;
        $categoryId = $book->category->id;

        // Generate category prefix based on category ID
        $catPrefix = (int) ($categoryId / 26).$alphabet[($categoryId % 26) - 1];

        // Get the first four characters of the book's name
        $bookNamePrefix = substr($book->name, 0, 4);
        $seriesLetter = alpha($bookNamePrefix);

        // If the book is part of a series
        if ($book->series_id) {
            $lastSeriesBooks = Book::where('category_id', $categoryId)
                ->whereNotNull('series_id')
                ->orderBy('id', 'DESC')
                ->take(2)
                ->get();

            if ($lastSeriesBooks->count() > 1) {
                // Retrieve the previous series book's number, increment if above 800
                $lastSeriesNumber = substr($lastSeriesBooks[1]->copy->first()->barcode, 2, 3);
                $seriesLetter = ($lastSeriesNumber > 800) ? $lastSeriesNumber + 1 : 800;
            } else {
                // Start series numbering at 800
                $seriesLetter = 800;
            }
        }

        // Get book number within category
        $lastBookInCategory = Book::where('category_id', $categoryId)
            ->where('name', 'like', $bookNamePrefix.'%')
            ->latest()->first();

        if ($book->series_id) {
            // If the book is in a series, count previous books in the series
            $seriesCount = Book::where('category_id', $categoryId)
                ->where('series_id', $book->series_id)
                ->count();
            $bookNumber = str_pad($seriesCount, 3, '0', STR_PAD_LEFT);
        } elseif ($lastBookInCategory) {
            // For non-series books, generate next ID based on last barcode in the category
            $lastBarcode = (string) $book->copy->sortByDesc('id')->first()->barcode;
            $bookNumber = $lastBarcode ? str_pad(substr($lastBarcode, 5, 3), 3, '0', STR_PAD_LEFT) : '001';
        } else {
            $bookNumber = '001';
        }

        // Create barcode
        $newBarcode = $catPrefix.$seriesLetter.$bookNumber.str_pad($edition->partCode, 2, '0', STR_PAD_LEFT);

        // Check for existing copies and increment copy ID
        $lastCopy = Copy::where('barcode', 'like', $newBarcode.'%')->orderBy('id', 'DESC')->first();
        $copyNumber = $lastCopy ? str_pad((int) substr($lastCopy->barcode, -2) + 1, 2, '0', STR_PAD_LEFT) : '01';

        // Final barcode
        return $newBarcode.$copyNumber;

    }
}
