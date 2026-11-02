<?php

declare(strict_types=1);

// П'ять назв фільмів; sort() сортує латинські назви за алфавітом.
$movieTitles = ['Interstellar', 'Dune', 'Avatar', 'Tenet', 'Inception'];
sort($movieTitles);

// Ключі точно відповідають атрибутам Order з docs/lab3/class-diagram-reference.puml.
$order = [
    'id' => 1,
    'customerId' => 7,
    'screeningId' => 12,
    'status' => 'pending',
    'totalAmount' => 450.0,
    'expiresAt' => '2026-10-29T18:15:00+01:00',
];
?>
<h3>Фільми за алфавітом</h3>
<ul>
    <?php foreach ($movieTitles as $title) { ?>
        <li><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></li>
    <?php } ?>
</ul>
<h3>Замовлення Order: атрибути з UML</h3>
<ul>
    <?php foreach ($order as $key => $value) { ?>
        <li><strong><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>:</strong>
            <?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?></li>
    <?php } ?>
</ul>
