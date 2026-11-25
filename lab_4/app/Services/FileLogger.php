<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\LoggerInterface;

final class FileLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo '[FILE] '.$message;
    }
}
