<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypeError;

use function App\Services\applyDiscount;
use function App\Services\calculateTotal;

require_once __DIR__.'/../../app/Services/EntityService.php';

final class EntityServiceTest extends TestCase
{
    public function test_calculates_order_total(): void
    {
        self::assertSame(450.0, calculateTotal(150.0, 3));
        self::assertSame(0.0, calculateTotal(150.0, 0));
        self::assertSame(37.05, calculateTotal(12.35, 3));
    }

    public function test_strict_types_reject_numeric_string(): void
    {
        $this->expectException(TypeError::class);
        calculateTotal('150', 3);
    }

    public function test_rejects_negative_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        calculateTotal(150.0, -1);
    }

    public function test_valid_discounts_including_boundaries(): void
    {
        self::assertSame(405.0, applyDiscount(450.0, 10.0));
        self::assertSame(450.0, applyDiscount(450.0, 0.0));
        self::assertSame(0.0, applyDiscount(450.0, 100.0));
    }

    #[DataProvider('invalidDiscounts')]
    public function test_rejects_invalid_discount(float $discount): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Неприпустиме значення знижки!');
        applyDiscount(450.0, $discount);
    }

    public static function invalidDiscounts(): array
    {
        return [[-0.1], [100.1], [150.0], [NAN], [INF]];
    }
}
