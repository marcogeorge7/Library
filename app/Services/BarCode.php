<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Series;
use Exception;

class BarCode
{
    public static function toAlphabeticCode($id, $length = 3)
    {
        $alphabet = range('A', 'Z'); // Create an array with letters A to Z
        $idIndex = $id % 26;

        // Determine the numeric part of the prefix
        $numericPart = (int) ($id / 26);

        if ($idIndex === 0) {
            $idPrefix = ($numericPart - 1).'Z'; // Adjust for zero-based indexing
        } else {
            $idPrefix = $numericPart.$alphabet[$idIndex - 1];
        }

        $paddedNumericPart = '';

        if ($length != 0) {
            // Pad the numeric part to the specified length
            $paddedNumericPart = str_pad($numericPart, $length, '0', STR_PAD_LEFT);
        }

        return $paddedNumericPart.substr($idPrefix, -1); // Concatenate padded numeric part with the last letter
    }

    public static function fromAlphabeticCode($code, $length = 3)
    {
        // Check if the code is valid
        if (strlen($code) < $length + 1) {
            throw new Exception('Invalid code format.');
        }

        // Extract the numeric part and the letter
        $numericPart = substr($code, 0, $length); // Get the numeric part based on the specified length
        $letter = substr($code, -1); // Get the last character (the letter)

        // Ensure the numeric part has the correct length
        if (strlen($numericPart) != $length) {
            throw new Exception('Numeric part does not match the specified length.');
        }

        // Convert the letter back to its corresponding index
        $alphabet = range('A', 'Z');
        $letterIndex = array_search($letter, $alphabet);

        if ($letterIndex === false) {
            throw new Exception('Invalid letter in code.');
        }

        // Calculate the original ID
        $numericValue = intval($numericPart);
        $id = ($numericValue * 26) + ($letterIndex + 1); // Add 1 because 'A' corresponds to 1

        return $id;
    }

    public static function generate(Edition $edition, $bookCode): string
    {
        // Get copy details
        $book = $edition->book;
        $categoryId = $book->category->id;

        // Convert category ID to alphabetic code
        $categoryCode = self::toAlphabeticCode($categoryId, 1);

        if ($book->series_id) {
            // Convert series ID to alphabetic code
            $seriesLetter = self::toAlphabeticCode($book->series_id, 1);

            // Get the position of the book in its series
            $bookPositionInSeries = self::toAlphabeticCode($book->series->bookOrderInSeries($book->id), 0);

            $bookCode = '1'.$seriesLetter.$bookPositionInSeries;

        } else {
            $bookCode = '0'.self::toAlphabeticCode($book->id, 2);
        }
        // Convert book ID to alphabetic code

        // Edition number (assuming this attribute exists in Book model)
        $editionNumber = $book->editionOrderInBook($edition->id);

        return "{$categoryCode}{$bookCode}{$editionNumber}";
    }
}
