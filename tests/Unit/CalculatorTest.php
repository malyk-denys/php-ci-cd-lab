<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Calculator;
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase
{
    public function test_add_returns_sum(): void
    {
        $calculator = new Calculator();

        self::assertSame(5, $calculator->add(2, 3));
    }

    public function test_subtract_returns_difference(): void
    {
        $calculator = new Calculator();

        self::assertSame(3, $calculator->subtract(5, 2));
    }
}

