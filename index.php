<?php

declare(strict_types=1);
use Faker\Factory;

use function App\Services\applyDiscount;
use function App\Services\calculateTotal;
use function App\Services\digitsBeforeLetters;
use function App\Services\insertZerosAfterPositive;
use function App\Services\lastThreeWords;

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/Services/EntityService.php';
require_once __DIR__.'/app/Services/DataProcessor.php';

$faker = Factory::create();
$prices = [];
for ($i = 0; $i < 10; $i++) {
    $prices[] = $faker->numberBetween(100, 500);
}
$minThreshold = 250;
$filteredPrices = array_filter($prices, function (int $price) use ($minThreshold): bool {
    return $price > $minThreshold;
});
$numbers = [4, -2, 0, 7, -5, 1];
$text = 'Сьогодні ми бронюємо квитки до кінотеатру';
$mixed = 'a3Б1c9Я2';

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Лабораторна 3 · Варіант 3</title>
    <style>
        body{margin:0;background:#f1f5f9;color:#172438;font:17px/1.6 system-ui,sans-serif}
        main{max-width:950px;margin:auto;padding:36px 20px}header{margin-bottom:28px}
        h1{line-height:1.2}h2{font-size:23px;margin-top:0}h3{font-size:18px}
        section{background:white;border:1px solid #d5deea;border-radius:12px;padding:24px;margin:18px 0}
        .eyebrow{color:#365d9c;font-weight:700}code{background:#edf2f7;padding:4px 7px;border-radius:4px;overflow-wrap:anywhere}
        .error{color:#a52424}.done{color:#226440}footer{color:#52647c}li{overflow-wrap:anywhere}
    </style>
</head>
<body><main>
<header><p class="eyebrow">ВЕБ-ПРОГРАМУВАННЯ / ВАРІАНТ 3</p>
<h1>Основи PHP: структури даних, функції</h1>
<p>Предметна галузь: система продажу квитків до кінотеатру.</p></header>
<section><h2>01 / Масиви та сутність Order</h2>
<?php require __DIR__.'/app/Models/EntityArray.php'; ?>
</section>
<section><h2>02 / Типізована функція</h2>
<p>Order::calculateTotal → calculateTotal(float $price, int $quantity): float</p>
<p>3 квитки × 150 грн = <strong><?= calculateTotal(150.0, 3) ?> грн</strong>.</p>
<p>Знижка 10% на 450 грн: <strong><?= applyDiscount(450.0, 10.0) ?> грн</strong>.</p></section>
<section><h2>03 / Faker та замикання з use</h2>
<p>10 випадкових цін квитків (100–500 грн):</p>
<p><code><?= escape(implode(', ', $prices)) ?></code></p>
<p>Ціни, строго більші за <?= $minThreshold ?> грн:</p>
<p><code><?= escape(implode(', ', $filteredPrices) ?: 'Немає відповідних цін') ?></code></p>
<p>Під час оновлення сторінки Faker генерує нові значення.</p></section>
<section><h2>04 / try · catch · finally</h2>
<p>Спроба застосувати некоректну знижку 150%:</p>
<?php
try {
    applyDiscount(450.0, 150.0);
} catch (Exception $e) {
    echo '<p class="error">Помилка: '.escape($e->getMessage()).'</p>';
} finally {
    echo '<p class="done">Завершення спроби розрахунку.</p>';
}
?>
</section>
<section><h2>05 / Алгоритми: задачі 2, 3, 7</h2>
<h3>Таблиця 3.2, №2 — нуль після додатного елемента</h3>
<p>Вхід: <code><?= escape(json_encode($numbers)) ?></code></p>
<p>Результат: <code><?= escape(json_encode(insertZerosAfterPositive($numbers))) ?></code></p>
<h3>Таблиця 3.3, №3 — три останні слова</h3>
<p>Вхід: <?= escape($text) ?></p>
<p>Результат: <code><?= escape(lastThreeWords($text)) ?></code></p>
<h3>Таблиця 3.4, №7 — спочатку цифри, потім букви</h3>
<p>Вхід: <code><?= escape($mixed) ?></code></p>
<p>Результат: <code><?= escape(digitsBeforeLetters($mixed)) ?></code></p>
<p>Початковий порядок усередині кожної групи збережено.</p></section>
<footer>Лабораторна робота №3 · PHP / Composer / Faker</footer>
</main></body></html>
