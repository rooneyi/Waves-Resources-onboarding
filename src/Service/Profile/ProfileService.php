<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Profile;

use App\DTO\Profile\ChangePasswordRequest;
use App\DTO\Profile\UpdateProfileRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class ProfileService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @return array{id: int, fullName: string, email: string, emailVerified: bool, roles: list<string>, createdAt: string, updatedAt: string}
     */
    public function getProfile(User $user): array
    {
        return $this->serialize($user);
    }

    /**
     * @return array{id: int, fullName: string, email: string, emailVerified: bool, roles: list<string>, createdAt: string, updatedAt: string}
     */
    public function updateProfile(User $user, UpdateProfileRequest $request): array
    {
        $user->setFullName(trim($request->fullName));
        $this->entityManager->flush();

        return $this->serialize($user);
    }

    public function changePassword(User $user, ChangePasswordRequest $request): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, $request->currentPassword)) {
            throw new BadRequestHttpException('Current password is incorrect.');
        }

        if ($request->currentPassword === $request->newPassword) {
            throw new BadRequestHttpException('New password must be different from the current password.');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $request->newPassword));
        $this->entityManager->flush();
    }

    /**
     * @return array{id: int, fullName: string, email: string, emailVerified: bool, roles: list<string>, createdAt: string, updatedAt: string}
     */
    private function serialize(User $user): array
    {
        return [
            'id' => (int) $user->getId(),
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'emailVerified' => $user->isEmailVerified(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $user->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
