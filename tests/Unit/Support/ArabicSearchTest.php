<?php

namespace Tests\Unit\Support;

use App\Support\ArabicSearch;
use PHPUnit\Framework\TestCase;

class ArabicSearchTest extends TestCase
{
    public function test_normalize_term_folds_alef_variants(): void
    {
        $expected = ArabicSearch::normalizeTerm('احمد');

        $this->assertSame($expected, ArabicSearch::normalizeTerm('أحمد'));
        $this->assertSame($expected, ArabicSearch::normalizeTerm('إحمد'));
        $this->assertSame($expected, ArabicSearch::normalizeTerm('آحمد'));
    }

    public function test_normalize_term_folds_yeh_and_alef_maksura(): void
    {
        $this->assertSame(ArabicSearch::normalizeTerm('مصطفي'), ArabicSearch::normalizeTerm('مصطفى'));
    }

    public function test_normalize_term_folds_heh_and_teh_marbuta(): void
    {
        $this->assertSame(ArabicSearch::normalizeTerm('مكتبه'), ArabicSearch::normalizeTerm('مكتبة'));
    }

    public function test_normalized_column_expression_wraps_column_in_nested_replace(): void
    {
        $expression = ArabicSearch::normalizedColumnExpression('name');

        $this->assertStringStartsWith('REPLACE(', $expression);
        $this->assertStringContainsString('name', $expression);
        $this->assertStringContainsString("'أ', 'ا'", $expression);
        $this->assertStringContainsString("'إ', 'ا'", $expression);
        $this->assertStringContainsString("'آ', 'ا'", $expression);
        $this->assertStringContainsString("'ى', 'ي'", $expression);
        $this->assertStringContainsString("'ة', 'ه'", $expression);
    }
}
