<?php

declare(strict_types=1);

namespace App\Service\Email;

use App\Entity\EmailVerificationToken;
use App\Entity\User;
use App\Repository\EmailVerificationTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class EmailVerificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailVerificationTokenRepository $tokenRepository,
        private MailerInterface $mailer,
        #[Autowire('%env(int:EMAIL_VERIFICATION_TOKEN_TTL)%')]
        private int $tokenTtlSeconds,
        #[Autowire('%env(MAILER_FROM)%')]
        private string $mailerFrom,
    ) {
    }

    /**
     * Creates a hashed verification token and returns the raw token for email delivery.
     */
    public function issueToken(User $user): string
    {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = new \DateTimeImmutable()->modify(sprintf('+%d seconds', $this->tokenTtlSeconds));

        $token = new EmailVerificationToken($user, $tokenHash, $expiresAt);
        $this->entityManager->persist($token);

        return $rawToken;
    }

    public function sendVerificationEmail(User $user, string $rawToken): void
    {
        $email = new Email()
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Verify your Waves account')
            ->text(sprintf(
                "Hello %s,\n\nPlease verify your email address using this token:\n\n%s\n\nThis token expires in %d hours.\n",
                $user->getFullName(),
                $rawToken,
                (int) ceil($this->tokenTtlSeconds / 3600),
            ));

        $this->mailer->send($email);
    }

    public function findValidByRawToken(string $rawToken): ?EmailVerificationToken
    {
        return $this->tokenRepository->findValidByHash(hash('sha256', $rawToken));
    }
}
