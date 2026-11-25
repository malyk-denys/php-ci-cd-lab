<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

final class User extends AbstractEntity
{
    public function __construct(
        string $id,
        string $createdAt,
        protected string $name,
        protected string $email,
    ) {
        parent::__construct($id, $createdAt);

        if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Потрібні непорожнє ім’я та коректна електронна адреса.');
        }
    }

    public function getDetails(): string
    {
        return sprintf('Користувач %s: %s <%s>', $this->id, $this->name, $this->email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
