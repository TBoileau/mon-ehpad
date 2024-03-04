<?php

namespace App\Core\Infrastructure\Doctrine\DataFixtures;

use App\Core\Infrastructure\Doctrine\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Ulid;

final class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->id = new Ulid();
        $user->email = 'user+1@email.com';
        $user->password = 'password';
        $manager->persist($user);

        $manager->flush();
    }
}
