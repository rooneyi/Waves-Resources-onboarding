<?php

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

final class RegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailVerificationService $emailVerificationService,
    ) {
    }

    public function register(RegisterRequest $request): User
    {
        if (null !== $this->userRepository->findOneByEmail($request->email)) {
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
