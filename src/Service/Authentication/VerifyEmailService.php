<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Authentication;

use App\DTO\Auth\VerifyEmailRequest;
use App\Entity\EmailVerificationToken;
use App\Entity\User;
use App\Service\Email\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class VerifyEmailService
{
    public function __construct(
        private EmailVerificationService $emailVerificationService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function verify(VerifyEmailRequest $request): User
    {
        $token = $this->emailVerificationService->findValidByRawToken($request->token);

        if (!$token instanceof EmailVerificationToken) {
            throw new BadRequestHttpException('Invalid or expired verification token.');
        }

        if ($token->isUsed() || $token->isExpired()) {
            throw new BadRequestHttpException('Invalid or expired verification token.');
        }

        $user = $token->getUser();
        $user->setEmailVerified(true);
        $token->markUsed();

        $this->entityManager->flush();

        return $user;
    }
}
