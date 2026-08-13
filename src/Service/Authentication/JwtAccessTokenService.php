<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Authentication;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class JwtAccessTokenService
{
    public function __construct(
        #[Autowire('%env(JWT_SECRET)%')]
        private string $jwtSecret,
        #[Autowire('%env(int:JWT_ACCESS_TOKEN_TTL)%')]
        private int $accessTokenTtl,
    ) {
    }

    public function create(User $user): string
    {
        $now = time();

        $payload = [
            'iss' => 'waves-resources-onboarding-api',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->accessTokenTtl,
            'sub' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * @return array{sub: string, email: string, roles: list<string>}
     */
    public function decode(string $token): array
    {
        $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

        return [
            'sub' => (string) $decoded->sub,
            'email' => (string) $decoded->email,
            'roles' => array_values((array) $decoded->roles),
        ];
    }

    public function getAccessTokenTtl(): int
    {
        return $this->accessTokenTtl;
    }
}
