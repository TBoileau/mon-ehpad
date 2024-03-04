<?php

declare(strict_types=1);

namespace Tests\Fixtures\Infrastructure\Doctrine\Repository;

use App\Security\Domain\Entity\User;
use App\Security\Domain\Repository\UserRepository;
use App\Security\Domain\ValueObject\Email;

final class FakeUserRepository implements UserRepository
{
    /** @var array<User> */
    public array $users = [];

    public function register(User $user): void
    {
        $this->users[] = $user;
    }

    public function isAlreadyUsed(Email $email): bool
    {
        return count(array_filter($this->users, static fn (User $user) => $user->email()->equals($email))) > 0;
    }
}
