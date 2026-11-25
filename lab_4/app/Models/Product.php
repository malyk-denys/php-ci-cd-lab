<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\ExportableInterface;
use App\Traits\JsonExportTrait;
use InvalidArgumentException;

final class Product extends AbstractEntity implements ExportableInterface
{
    use JsonExportTrait;

    public function __construct(
        string $id,
        string $createdAt,
        protected string $name,
        protected float $price,
    ) {
        parent::__construct($id, $createdAt);

        if ($name === '' || ! is_finite($price) || $price < 0) {
            throw new InvalidArgumentException('Назва має бути непорожньою, а ціна - невід’ємною.');
        }
    }

    public function getDetails(): string
    {
        return sprintf('Товар %s: %s, %.2f грн', $this->id, $this->name, $this->price);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
