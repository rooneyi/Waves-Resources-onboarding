<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => strtolower(trim($email))]);
    }

    /**
     * @return array{items: list<User>, total: int}
     */
    public function searchAdminUsers(
        int $page,
        int $limit,
        ?string $role,
        ?bool $verified,
        string $sort,
        string $direction,
    ): array {
        $qb = $this->createQueryBuilder('u');

        if (null !== $role) {
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%"'.$role.'"%');
        }

        if (null !== $verified) {
            $qb->andWhere('u.emailVerified = :verified')
                ->setParameter('verified', $verified);
        }

        $sortField = match ($sort) {
            'fullName' => 'u.fullName',
            default => 'u.createdAt',
        };
        $sortDirection = 'asc' === strtolower($direction) ? 'ASC' : 'DESC';
        $qb->orderBy($sortField, $sortDirection);

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(u.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->select('u')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}
