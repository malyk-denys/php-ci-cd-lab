<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

/** Таблиця 3.2, задача 2: після кожного додатного числа вставляємо нуль. */
function insertZerosAfterPositive(array $numbers): array
{
    $result = [];
    foreach ($numbers as $number) {
        if (! is_int($number)) {
            throw new InvalidArgumentException('Масив має містити лише цілі числа.');
        }
        $result[] = $number;
        if ($number > 0) {
            $result[] = 0;
        }
    }

    return $result;
}

/** Таблиця 3.3, задача 3: слова розділяються пробільними символами. */
function lastThreeWords(string $text): string
{
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    if ($words === false) {
        throw new InvalidArgumentException('Очікується коректний UTF-8.');
    }

    return implode(' ', array_slice($words, -3));
}

/** Таблиця 3.4, задача 7: стабільне групування цифр і букв Unicode. */
function digitsBeforeLetters(string $text): string
{
    if (preg_match('/\A[\p{L}\p{Nd}]*\z/u', $text) !== 1) {
        throw new InvalidArgumentException('Рядок має містити лише цифри та букви UTF-8.');
    }

    return preg_replace('/\p{L}/u', '', $text).preg_replace('/\p{Nd}/u', '', $text);
}
