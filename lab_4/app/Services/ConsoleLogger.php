<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\LoggerInterface;

final class ConsoleLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo '[CONSOLE] '.$message;
    }
}
