<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function App\Services\digitsBeforeLetters;
use function App\Services\insertZerosAfterPositive;
use function App\Services\lastThreeWords;

require_once __DIR__.'/../../app/Services/DataProcessor.php';

final class DataProcessorTest extends TestCase
{
    #[DataProvider('arrayCases')]
    public function test_inserts_zeros(array $input, array $expected): void
    {
        self::assertSame($expected, insertZerosAfterPositive($input));
    }

    public static function arrayCases(): array
    {
        return [
            [[4, -2, 0, 7, -5, 1], [4, 0, -2, 0, 7, 0, -5, 1, 0]],
            [[], []], [[0, -1], [0, -1]], [[1, 2], [1, 0, 2, 0]],
            [[PHP_INT_MAX, PHP_INT_MIN], [PHP_INT_MAX, 0, PHP_INT_MIN]],
        ];
    }

    public function test_rejects_non_integer_element(): void
    {
        $this->expectException(InvalidArgumentException::class);
        insertZerosAfterPositive([1, '2']);
    }

    #[DataProvider('wordCases')]
    public function test_last_words(string $input, string $expected): void
    {
        self::assertSame($expected, lastThreeWords($input));
    }

    public static function wordCases(): array
    {
        return [
            ['Сьогодні ми бронюємо квитки до кінотеатру', 'квитки до кінотеатру'],
            ['', ''], [" \t\n", ''], ['два слова', 'два слова'],
            [" один\tдва\nтри   чотири ", 'два три чотири'],
            ['Фільм: Дюна, сеанс сьогодні!', 'Дюна, сеанс сьогодні!'],
            ["один\u{00A0}два\u{00A0}три\u{00A0}чотири", 'два три чотири'],
        ];
    }

    #[DataProvider('characterCases')]
    public function test_groups_characters_stably(string $input, string $expected): void
    {
        self::assertSame($expected, digitsBeforeLetters($input));
    }

    public static function characterCases(): array
    {
        return [['a3Б1c9Я2', '3192aБcЯ'], ['', ''], ['abc', 'abc'],
            ['930', '930'], ['ї0Є9ґ1', '091їЄґ'], ['а٢B１', '٢１аB']];
    }

    public function test_rejects_characters_outside_task_domain(): void
    {
        $this->expectException(InvalidArgumentException::class);
        digitsBeforeLetters('a 1!');
    }

    public function test_rejects_invalid_utf8(): void
    {
        $this->expectException(InvalidArgumentException::class);
        lastThreeWords("\xFF");
    }
}
