<?php

declare(strict_types=1);

namespace App\Tasks;

use InvalidArgumentException;

final class Worker
{
    public function __construct(
        public readonly float $base,
        public readonly int $productCount,
        public readonly float $productPrice,
    ) {
        if (! is_finite($base) || $base < 0 || $productCount < 0 || ! is_finite($productPrice) || $productPrice < 0) {
            throw new InvalidArgumentException('Параметри зарплати мають бути скінченними невід’ємними числами.');
        }
    }

    public function calculateSalary(): float
    {
        return round($this->base + ($this->productCount * 0.05 * $this->productPrice), 2);
    }
}
