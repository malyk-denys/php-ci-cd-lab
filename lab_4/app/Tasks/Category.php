<?php

declare(strict_types=1);

namespace App\Tasks;

use InvalidArgumentException;

final class Category
{
    /** @var array<int, Book> */
    private array $books = [];

    public function __construct(
        public readonly int $categoryId,
        public readonly string $name,
    ) {
        if ($categoryId <= 0 || $name === '') {
            throw new InvalidArgumentException('Категорія повинна мати id і назву.');
        }
    }

    public function addBook(Book $book): void
    {
        $this->books[$book->id] = $book;
    }

    public function removeBook(int $id): bool
    {
        if (! isset($this->books[$id])) {
            return false;
        }

        unset($this->books[$id]);

        return true;
    }

    /** @return list<Book> */
    public function searchBook(string $title): array
    {
        if ($title === '') {
            return [];
        }

        return array_values(array_filter(
            $this->books,
            static fn (Book $book): bool => self::contains($book->title, $title),
        ));
    }

    /** @return list<Book> */
    public function getBooks(): array
    {
        return array_values($this->books);
    }

    private static function contains(string $haystack, string $needle): bool
    {
        if (function_exists('mb_stripos')) {
            return mb_stripos($haystack, $needle, 0, 'UTF-8') !== false;
        }

        return stripos($haystack, $needle) !== false;
    }
}
