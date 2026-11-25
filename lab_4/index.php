<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\EntityFactory;
use App\Interfaces\ExportableInterface;
use App\Services\ConsoleLogger;
use App\Services\EntityService;
use App\Tasks\Book;
use App\Tasks\Category;
use App\Tasks\DieselEngine;
use App\Tasks\TurbojetEngine;
use App\Tasks\Worker;

require_once __DIR__.'/vendor/autoload.php';

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

ob_start();
$database = Database::getInstance();
$databaseMessage = (string) ob_get_clean();
$sameDatabase = $database === Database::getInstance();

$factory = new EntityFactory;
$product = $factory->createEntity('product', [
    'id' => 'ticket-101',
    'createdAt' => '2026-11-08 10:00:00',
    'name' => 'Квиток на Interstellar',
    'price' => 220.0,
]);
$user = $factory->createEntity('user', [
    'id' => 'user-7',
    'createdAt' => '2026-11-08 10:05:00',
    'name' => 'Денис Малик',
    'email' => 'denys@example.com',
]);

if (! $product instanceof ExportableInterface) {
    throw new LogicException('Товар має підтримувати експорт у JSON.');
}

$logger = new ConsoleLogger;
$service = new EntityService($logger);
ob_start();
$productDetails = $service->describe($product);
$logMessage = (string) ob_get_clean();

$category = new Category(3, 'Наукова фантастика');
$category->addBook(new Book(1, 'Дюна', ['Френк Герберт'], 1965));
$category->addBook(new Book(2, 'Соляріс', ['Станіслав Лем'], 1961));
$foundBooks = $category->searchBook('Дюна');

$diesel = new DieselEngine('D-12');
$turbojet = new TurbojetEngine('TJ-900');
$worker = new Worker(18000.0, 120, 250.0);
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Лабораторна 4 · ООП у PHP · Варіант 3</title>
    <style>
        :root{color-scheme:light;--ink:#182235;--muted:#53647a;--line:#d8e0eb;--accent:#285a9f;--bg:#eef3f8}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:17px/1.58 system-ui,sans-serif}
        main{max-width:980px;margin:auto;padding:38px 20px 54px}header{margin-bottom:30px}h1{max-width:820px;line-height:1.18}
        h2{font-size:23px;margin:0 0 12px}h3{font-size:18px;margin:20px 0 8px}.eyebrow{color:var(--accent);font-weight:750}
        section{background:#fff;border:1px solid var(--line);border-radius:14px;padding:25px;margin:18px 0;box-shadow:0 8px 25px #1e35530a}
        code{display:block;background:#f3f6fa;border-radius:7px;padding:12px;white-space:pre-wrap;overflow-wrap:anywhere}
        .ok{color:#1f6d43;font-weight:700}ul{padding-left:22px}footer{color:var(--muted);margin-top:28px}
    </style>
</head>
<body><main>
<header>
    <p class="eyebrow">ВЕБ-ПРОГРАМУВАННЯ / ЛАБОРАТОРНА 4 / ВАРІАНТ 3</p>
    <h1>ООП у PHP: наслідування, трейти, контракти та патерни проєктування</h1>
    <p>Демонстрація абстракцій, Singleton, Factory, Dependency Injection та індивідуальних класів.</p>
</header>

<section>
    <h2>01 / Singleton Database</h2>
    <p><?= escape($databaseMessage) ?></p>
    <p class="ok">Повторний виклик повернув той самий об’єкт: <?= $sameDatabase ? 'так' : 'ні' ?></p>
</section>

<section>
    <h2>02 / AbstractEntity та EntityFactory</h2>
    <ul>
        <li><?= escape($product->getDetails()) ?></li>
        <li><?= escape($user->getDetails()) ?></li>
    </ul>
</section>

<section>
    <h2>03 / Інтерфейс і трейт JSON</h2>
    <code><?= escape($product->exportToJson()) ?></code>
</section>

<section>
    <h2>04 / Dependency Injection</h2>
    <p><?= escape($productDetails) ?></p>
    <code><?= escape($logMessage) ?></code>
</section>

<section>
    <h2>05 / Індивідуальне завдання варіанта 3</h2>
    <h3>Композиція Book і Category</h3>
    <p>У категорії «<?= escape($category->name) ?>» знайдено: <?= escape($foundBooks[0]->title ?? 'нічого') ?>.</p>
    <h3>Ієрархія двигунів</h3>
    <ul>
        <li><?= escape($diesel->startEngine()) ?></li>
        <li><?= escape($turbojet->startEngine()) ?></li>
    </ul>
    <h3>Інкапсуляція алгоритму Worker</h3>
    <p>Зарплата за формулою Base + PCount × 0.05 × PP: <strong><?= number_format($worker->calculateSalary(), 2, ',', ' ') ?> грн</strong>.</p>
</section>

<footer>PHP 8.2 · PSR-4 · PHPUnit · Laravel Pint</footer>
</main></body>
</html>
