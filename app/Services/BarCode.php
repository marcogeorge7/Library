<?php

namespace App\Services;

use App\Models\Edition;
use Exception;

class BarCode
{
    private const ALPHABET = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
        'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    private const ALPHABET_SIZE = 26;

    private const STANDALONE_POSITION = 1;

    public static function toAlphabeticCode(int $id, int $length = 3): string
    {
        $idIndex = $id % self::ALPHABET_SIZE;
        $numericPart = (int) ($id / self::ALPHABET_SIZE);

        $letter = $idIndex === 0
            ? self::ALPHABET[self::ALPHABET_SIZE - 1]
            : self::ALPHABET[$idIndex - 1];

        if ($idIndex === 0) {
            $numericPart--;
        }

        return $length === 0
            ? $letter
            : str_pad($numericPart, $length, '0', STR_PAD_LEFT).$letter;
    }

    public static function fromAlphabeticCode(string $code, int $length = 3): int
    {
        self::validateCode($code, $length);

        $numericPart = substr($code, 0, $length);
        $letter = substr($code, -1);

        $letterIndex = array_search($letter, self::ALPHABET);

        if ($letterIndex === false) {
            throw new Exception('Invalid letter in code.');
        }

        return (intval($numericPart) * self::ALPHABET_SIZE) + ($letterIndex + 1);
    }

    public static function generate(Edition $edition): string
    {
        $book = $edition->book;
        $categoryCode = self::toAlphabeticCode($book->category->id, 1);

        // book->order is the group's shared slot in the category: every book in a
        // series shares the same value (inherited from whichever book first claimed
        // it -- see BooksImport::resolveSeriesAndOrder() / CreateBook), and a
        // standalone book's own value doubles as its one-member group's slot.
        $groupOrder = self::padNumber($book->order);

        // Plain 2-digit number, not the alphabetic scheme used for category. Position
        // of this book within its group, in join order -- the 1st book of a series
        // (or a standalone book, which is always alone) is "01", the 2nd is "02", etc.
        // Most series have well under 100 members, so this is unique within nearly
        // every series; the %100 wrap mirrors the same collision tradeoff already
        // accepted for the part/edition segments on implausibly large counts.
        $memberPosition = $book->series_id
            ? self::padNumber($book->series->bookOrderInSeries($book->id) % 100, 2)
            : self::padNumber(self::STANDALONE_POSITION, 2);

        $partCode = self::padNumber($edition->partCode, 2);
        $editionNumber = $book->editionOrderInBook($edition->id);

        return "{$categoryCode}{$groupOrder}{$memberPosition}{$partCode}{$editionNumber}";
    }

    private static function validateCode(string $code, int $length): void
    {
        if (strlen($code) < $length + 1) {
            throw new Exception('Invalid code format.');
        }

        if (strlen(substr($code, 0, $length)) !== $length) {
            throw new Exception('Numeric part does not match the specified length.');
        }
    }

    private static function padNumber(int $number, int $length = 3): string
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }
}
