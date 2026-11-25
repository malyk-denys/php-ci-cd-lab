<?php

declare(strict_types=1);

namespace App\Models;

abstract class AbstractEntity
{
    public function __construct(
        protected string $id,
        protected string $createdAt,
    ) {}

    abstract public function getDetails(): string;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
