<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Authentication;

use App\DTO\Auth\LoginRequest;
use App\DTO\Auth\LogoutRequest;
use App\DTO\Auth\RefreshTokenRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthenticationService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JwtAccessTokenService $jwtAccessTokenService,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{accessToken: string, refreshToken: string, expiresIn: int, tokenType: string}
     */
    public function login(LoginRequest $request): array
    {
        $user = $this->userRepository->findOneByEmail($request->email);

        if (null === $user || !$this->passwordHasher->isPasswordValid($user, $request->password)) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid credentials.');
        }

        if (!$user->isEmailVerified()) {
            throw new AccessDeniedHttpException('Email address has not been verified.');
        }

        return $this->issueTokenPair($user);
    }

    /**
     * @return array{accessToken: string, refreshToken: string, expiresIn: int, tokenType: string}
     */
    public function refresh(RefreshTokenRequest $request): array
    {
        $refreshToken = $this->refreshTokenService->findValid($request->refreshToken);

        if (null === $refreshToken) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid refresh token.');
        }

        $user = $refreshToken->getUser();

        if (!$user->isEmailVerified()) {
            throw new AccessDeniedHttpException('Email address has not been verified.');
        }

        $this->refreshTokenService->revoke($refreshToken);

        return $this->issueTokenPair($user);
    }

    public function logout(LogoutRequest $request): void
    {
        $refreshToken = $this->refreshTokenService->findValid($request->refreshToken);

        if (null === $refreshToken) {
            return;
        }

        $this->refreshTokenService->revoke($refreshToken);
        $this->entityManager->flush();
    }

    /**
     * @return array{accessToken: string, refreshToken: string, expiresIn: int, tokenType: string}
     */
    private function issueTokenPair(User $user): array
    {
        $accessToken = $this->jwtAccessTokenService->create($user);
        $refresh = $this->refreshTokenService->issue($user);
        $this->entityManager->flush();

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refresh['rawToken'],
            'expiresIn' => $this->jwtAccessTokenService->getAccessTokenTtl(),
            'tokenType' => 'Bearer',
        ];
    }
}
