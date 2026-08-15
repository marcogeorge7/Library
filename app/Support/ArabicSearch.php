<?php

namespace App\Support;

class ArabicSearch
{
    private const EQUIVALENCE_GROUPS = [
        'ا' => ['أ', 'إ', 'آ'],
        'ي' => ['ى'],
        'ه' => ['ة'],
    ];

    public static function normalizeTerm(string $value): string
    {
        return strtr($value, self::replacementMap());
    }

    /**
     * Nested SQL REPLACE() calls folding every variant down to its canonical
     * letter, so comparing normalized(column) against normalized(term) matches
     * regardless of which variant is actually stored/typed. Portable across
     * sqlite and mysql (both support nested REPLACE()).
     */
    public static function normalizedColumnExpression(string $column): string
    {
        $expression = $column;

        foreach (self::replacementMap() as $variant => $canonical) {
            $expression = "REPLACE({$expression}, '{$variant}', '{$canonical}')";
        }

        return $expression;
    }

    private static function replacementMap(): array
    {
        $map = [];

        foreach (self::EQUIVALENCE_GROUPS as $canonical => $variants) {
            foreach ($variants as $variant) {
                $map[$variant] = $canonical;
            }
        }

        return $map;
    }
}
