<?php

declare(strict_types=1);

namespace App\Services;

final class Calculator
{
    public function add(int $x, int $y): int
    {
        return $x + $y;
    }

    public function subtract(int $x, int $y): int
    {
        return $x - $y;
    }
}
