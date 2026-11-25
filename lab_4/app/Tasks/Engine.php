<?php

declare(strict_types=1);

namespace App\Tasks;

abstract class Engine
{
    public function __construct(protected string $name) {}

    abstract public function startEngine(): string;

    protected function getRoleDetails(): string
    {
        return "Двигун {$this->name}";
    }
}
