<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Tests\Functional\Auth;

use App\Entity\User;
use App\Service\Email\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AuthTestCase extends WebTestCase
{
    protected function registerVerifiedUser(KernelBrowser $client, string $email, string $password = 'SecurePass1'): void
    {
        $client->request(
            'POST',
            '/api/v1/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'fullName' => 'Auth User',
                'email' => $email,
                'password' => $password,
            ], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);

        $rawToken = static::getContainer()->get(EmailVerificationService::class)->issueToken($user);
        $em->flush();

        $client->request(
            'POST',
            '/api/v1/auth/verify-email',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['token' => $rawToken], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
