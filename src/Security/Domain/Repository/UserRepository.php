<?php

declare(strict_types=1);

namespace App\Security\Domain\Repository;

use App\Security\Domain\Entity\User;
use App\Security\Domain\ValueObject\Email;

interface UserRepository
{
    public function register(User $user): void;

    public function isAlreadyUsed(Email $email): bool;
}
