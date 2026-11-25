<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Database;
use App\Core\EntityFactory;
use App\Interfaces\LoggerInterface;
use App\Models\Product;
use App\Models\User;
use App\Services\EntityService;
use App\Tasks\Book;
use App\Tasks\Category;
use App\Tasks\DieselEngine;
use App\Tasks\TurbojetEngine;
use App\Tasks\Worker;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class Lab4ArchitectureTest extends TestCase
{
    public function test_database_is_a_singleton(): void
    {
        ob_start();
        $first = Database::getInstance();
        $firstOutput = (string) ob_get_clean();

        ob_start();
        $second = Database::getInstance();
        $secondOutput = (string) ob_get_clean();

        self::assertSame($first, $second);
        self::assertSame('Підключення до БД створено', $firstOutput);
        self::assertSame('', $secondOutput);
    }

    public function test_factory_creates_supported_entities(): void
    {
        $factory = new EntityFactory;
        $product = $factory->createEntity('product', [
            'id' => 'p-1',
            'createdAt' => '2026-11-08 10:00:00',
            'name' => 'Квиток',
            'price' => 220,
        ]);
        $user = $factory->createEntity('user', [
            'id' => 'u-1',
            'createdAt' => '2026-11-08 10:00:00',
            'name' => 'Денис',
            'email' => 'denys@example.com',
        ]);

        self::assertInstanceOf(Product::class, $product);
        self::assertInstanceOf(User::class, $user);
        self::assertStringContainsString('220.00 грн', $product->getDetails());
        self::assertStringContainsString('denys@example.com', $user->getDetails());
    }

    public function test_factory_rejects_unknown_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new EntityFactory)->createEntity('order', []);
    }

    public function test_product_exports_inherited_and_own_properties_to_json(): void
    {
        $product = new Product('p-1', '2026-11-08', 'Квиток', 220.0);
        $data = json_decode($product->exportToJson(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame([
            'id' => 'p-1',
            'createdAt' => '2026-11-08',
            'name' => 'Квиток',
            'price' => 220.0,
        ], $data);
    }

    public function test_entity_service_receives_logger_through_dependency_injection(): void
    {
        $logger = new class implements LoggerInterface
        {
            /** @var list<string> */
            public array $messages = [];

            public function log(string $message): void
            {
                $this->messages[] = $message;
            }
        };
        $service = new EntityService($logger);
        $product = new Product('p-5', '2026-11-08', 'Квиток', 180.0);

        self::assertSame('Товар p-5: Квиток, 180.00 грн', $service->describe($product));
        self::assertSame(['Оброблено сутність p-5'], $logger->messages);
    }

    public function test_category_adds_searches_and_removes_books(): void
    {
        $category = new Category(3, 'Наукова фантастика');
        $dune = new Book(1, 'Дюна', ['Френк Герберт'], 1965);
        $solaris = new Book(2, 'Соляріс', ['Станіслав Лем'], 1961);
        $category->addBook($dune);
        $category->addBook($solaris);

        self::assertSame([$dune], $category->searchBook('Дюна'));
        self::assertTrue($category->removeBook(1));
        self::assertFalse($category->removeBook(999));
        self::assertSame([$solaris], $category->getBooks());
    }

    public function test_engine_hierarchy_extends_parent_behavior(): void
    {
        $dieselMessage = (new DieselEngine('D-12'))->startEngine();
        $turbojetMessage = (new TurbojetEngine('TJ-900'))->startEngine();

        self::assertStringContainsString('стискання', $dieselMessage);
        self::assertStringContainsString('стискання', $turbojetMessage);
        self::assertStringContainsString('турбореактивну тягу', $turbojetMessage);
    }

    public function test_worker_calculates_salary_by_variant_formula(): void
    {
        $worker = new Worker(18000.0, 120, 250.0);

        self::assertSame(19500.0, $worker->calculateSalary());
    }

    public function test_worker_rejects_negative_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Worker(18000.0, -1, 250.0);
    }
}
