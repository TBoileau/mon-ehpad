<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use App\Security\Domain\Entity\User as SecurityUser;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[Entity]
#[Table(name: '`user`')]
class User
{
    #[Id]
    #[Column(type: UlidType::NAME)]
    public Ulid $id;

    #[Column(type: Types::STRING)]
    public string $email;

    #[Column(type: Types::STRING)]
    public string $password;

    public static function fromSecurityUser(SecurityUser $user): self
    {
        $entity = new self();
        $entity->id = $user->id()->value();
        $entity->email = $user->email()->value();
        $entity->password = $user->password()->value();

        return $entity;
    }
}
