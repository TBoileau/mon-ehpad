<?php

declare(strict_types=1);

namespace Tests\Component\Doctrine\Repository;

use App\Core\Domain\ValueObject\Identifier;
use App\Core\Infrastructure\Doctrine\Repository\UserDoctrineRepository;
use App\Security\Domain\Entity\User;
use App\Security\Domain\Repository\UserRepository;
use App\Security\Domain\ValueObject\Email;
use App\Security\Domain\ValueObject\Password;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testRegister(): void
    {
        $container = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserDoctrineRepository::class);

        $user = new User(
            Identifier::generate(),
            Email::create('user@email.com'),
            Password::create('password')
        );

        $userRepository->register($user);

        self::assertTrue($userRepository->isAlreadyUsed(Email::create('user@email.com')));
    }

    public function testIsAlreadyRegistered(): void
    {
        $container = static::getContainer();

        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserDoctrineRepository::class);

        self::assertTrue($userRepository->isAlreadyUsed(Email::create('user+1@email.com')));

        self::assertFalse($userRepository->isAlreadyUsed(Email::create('user@email.com')));
    }
}
