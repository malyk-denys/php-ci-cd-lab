<?php

declare(strict_types=1);

namespace App\Interfaces;

interface LoggerInterface
{
    public function log(string $message): void;
}
