<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Infrastructure\Doctrine\Entity\User as DoctrineUser;
use App\Security\Domain\Entity\User;
use App\Security\Domain\Repository\UserRepository;
use App\Security\Domain\ValueObject\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserDoctrineRepository extends ServiceEntityRepository implements UserRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctrineUser::class);
    }

    public function register(User $user): void
    {
        $this->getEntityManager()->persist(DoctrineUser::fromSecurityUser($user));
        $this->getEntityManager()->flush();
    }

    public function isAlreadyUsed(Email $email): bool
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.email = :email')
            ->setParameter('email', $email->value())
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
