<?php

declare(strict_types=1);

namespace App\Core\Domain\ValueObject;

use Symfony\Component\Uid\Ulid;

final class Identifier
{
    public function __construct(private Ulid $value)
    {
    }

    public static function generate(): self
    {
        return new self(new Ulid());
    }

    public function value(): Ulid
    {
        return $this->value;
    }
}
