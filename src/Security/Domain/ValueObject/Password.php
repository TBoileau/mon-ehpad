<?php

declare(strict_types=1);

namespace App\Security\Domain\ValueObject;

final class Password
{
    private function __construct(private string $value)
    {
    }

    public static function create(string $password): self
    {
        return new self($password);
    }

    public function value(): string
    {
        return $this->value;
    }
}
