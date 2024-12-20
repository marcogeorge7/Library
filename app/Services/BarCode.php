<?php

namespace App\Services;

use App\Models\Edition;
use Exception;

class BarCode
{
    private const ALPHABET = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
        'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    private const ALPHABET_SIZE = 26;

    private const DEFAULT_SERIES_POSITION = '01';

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
        $bookOrder = self::padNumber($book->order);

        $bookInSeries = $book?->series?->bookOrderInSeries($book->id) ?? null;

        $seriesCode = $book->series_id
            ? self::padNumber($bookInSeries, 2)
            : self::DEFAULT_SERIES_POSITION;

        $bookCode = $bookOrder.$seriesCode;
        $editionNumber = $book->editionOrderInBook($edition->id);

        return "{$categoryCode}{$bookCode}{$editionNumber}";
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
