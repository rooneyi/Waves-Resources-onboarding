<?php

declare(strict_types=1);

namespace App\Service\Authentication;

use App\DTO\Auth\RegisterRequest;
use App\Entity\User;
use App\Enum\Role;
use App\Repository\UserRepository;
use App\Service\Email\EmailVerificationService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailVerificationService $emailVerificationService,
    ) {
    }

    public function register(RegisterRequest $request): User
    {
        if ($this->userRepository->findOneByEmail($request->email) instanceof User) {
            throw new ConflictHttpException('An account with this email already exists.');
        }

        $user = new User();
        $user->setFullName(trim($request->fullName));
        $user->setEmail($request->email);
        $user->setRoles([Role::User->value]);
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));
        $user->setEmailVerified(false);

        $this->entityManager->persist($user);

        $rawToken = $this->emailVerificationService->issueToken($user);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            throw new ConflictHttpException('An account with this email already exists.');
        }

        $this->emailVerificationService->sendVerificationEmail($user, $rawToken);

        return $user;
    }
}
