<?php

declare(strict_types=1);

namespace App\Tasks;

use InvalidArgumentException;

final class Book
{
    /** @param list<string> $authors */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly array $authors,
        public readonly int $pubYear,
    ) {
        if ($id <= 0 || $title === '' || $authors === [] || $pubYear <= 0) {
            throw new InvalidArgumentException('Книга повинна мати id, назву, авторів і рік видання.');
        }

        foreach ($authors as $author) {
            if (! is_string($author) || $author === '') {
                throw new InvalidArgumentException('Імена авторів мають бути непорожніми рядками.');
            }
        }
    }
}
