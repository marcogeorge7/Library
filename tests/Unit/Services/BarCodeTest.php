<?php

namespace Tests\Unit\Services;

use App\Services\BarCode;
use PHPUnit\Framework\TestCase;

class BarCodeTest extends TestCase
{
    public function test_known_alphabetic_codes(): void
    {
        $this->assertSame('0A', BarCode::toAlphabeticCode(1, 1));
        $this->assertSame('0Z', BarCode::toAlphabeticCode(26, 1));
        $this->assertSame('1A', BarCode::toAlphabeticCode(27, 1));
    }

    public function test_alphabetic_code_round_trips(): void
    {
        foreach ([1, 2, 25, 26, 27, 52, 100, 260, 1580] as $id) {
            $code = BarCode::toAlphabeticCode($id, 1);
            $numericLength = strlen($code) - 1;

            $this->assertSame($id, BarCode::fromAlphabeticCode($code, $numericLength));
        }
    }
}
