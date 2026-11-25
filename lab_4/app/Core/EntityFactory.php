<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AbstractEntity;
use App\Models\Product;
use App\Models\User;
use InvalidArgumentException;

final class EntityFactory
{
    public function createEntity(string $type, array $data): AbstractEntity
    {
        return match (strtolower($type)) {
            'product' => new Product(
                $this->stringValue($data, 'id'),
                $this->stringValue($data, 'createdAt'),
                $this->stringValue($data, 'name'),
                $this->floatValue($data, 'price'),
            ),
            'user' => new User(
                $this->stringValue($data, 'id'),
                $this->stringValue($data, 'createdAt'),
                $this->stringValue($data, 'name'),
                $this->stringValue($data, 'email'),
            ),
            default => throw new InvalidArgumentException("Невідомий тип сутності: {$type}"),
        };
    }

    private function stringValue(array $data, string $key): string
    {
        if (! isset($data[$key]) || ! is_string($data[$key])) {
            throw new InvalidArgumentException("Поле {$key} має бути рядком.");
        }

        return $data[$key];
    }

    private function floatValue(array $data, string $key): float
    {
        if (! isset($data[$key]) || ! is_int($data[$key]) && ! is_float($data[$key])) {
            throw new InvalidArgumentException("Поле {$key} має бути числом.");
        }

        return (float) $data[$key];
    }
}
