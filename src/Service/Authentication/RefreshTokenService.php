<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Authentication;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class RefreshTokenService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RefreshTokenRepository $refreshTokenRepository,
        #[Autowire('%env(int:JWT_REFRESH_TOKEN_TTL)%')]
        private int $refreshTokenTtl,
    ) {
    }

    /**
     * @return array{rawToken: string, entity: RefreshToken}
     */
    public function issue(User $user): array
    {
        $rawToken = bin2hex(random_bytes(32));
        $expiresAt = new \DateTimeImmutable()->modify(sprintf('+%d seconds', $this->refreshTokenTtl));
        $entity = new RefreshToken($user, hash('sha256', $rawToken), $expiresAt);

        $this->entityManager->persist($entity);

        return [
            'rawToken' => $rawToken,
            'entity' => $entity,
        ];
    }

    public function findValid(string $rawToken): ?RefreshToken
    {
        return $this->refreshTokenRepository->findValidByHash(hash('sha256', $rawToken));
    }

    public function revoke(RefreshToken $token): void
    {
        $token->revoke();
    }

    public function getRefreshTokenTtl(): int
    {
        return $this->refreshTokenTtl;
    }
}
