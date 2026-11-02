<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use InvalidArgumentException;

/** Процедурна реалізація операції Order::calculateTotal з UML лабораторної 2. */
function calculateTotal(float $price, int $quantity): float
{
    if (! is_finite($price) || $price < 0 || $quantity < 0) {
        throw new InvalidArgumentException('Ціна та кількість мають бути невід’ємними.');
    }
    $total = $price * $quantity;
    if (! is_finite($total)) {
        throw new InvalidArgumentException('Сума замовлення завелика.');
    }

    return round($total, 2);
}

function applyDiscount(float $price, float $discount): float
{
    if (! is_finite($discount) || $discount < 0 || $discount > 100) {
        throw new Exception('Неприпустиме значення знижки!');
    }
    if (! is_finite($price) || $price < 0) {
        throw new InvalidArgumentException('Ціна має бути невід’ємним скінченним числом.');
    }

    return round($price * (1 - $discount / 100), 2);
}
