<?php

declare(strict_types=1);

namespace App\Core;

use LogicException;

final class Database
{
    private static ?self $instance = null;

    private function __construct()
    {
        echo 'Підключення до БД створено';
    }

    private function __clone() {}

    public function __wakeup(): never
    {
        throw new LogicException('Singleton не можна десеріалізувати.');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
