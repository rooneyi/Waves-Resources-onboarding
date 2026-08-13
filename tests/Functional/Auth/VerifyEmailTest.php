<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Tests\Functional\Auth;

use App\Entity\EmailVerificationToken;
use App\Entity\User;
use App\Service\Email\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class VerifyEmailTest extends WebTestCase
{
    public function testValidTokenVerifiesEmail(): void
    {
        $client = self::createClient();
        $email = sprintf('verify.%s@example.com', bin2hex(random_bytes(4)));
        $this->register($client, $email);
        $rawToken = $this->issueKnownToken($email);

        $client->request(
            'POST',
            '/api/v1/auth/verify-email',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['token' => $rawToken], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame($email, $payload['email']);
        self::assertTrue($payload['emailVerified']);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $client = self::createClient();
        $email = sprintf('expired.%s@example.com', bin2hex(random_bytes(4)));
        $this->register($client, $email);

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);

        $rawToken = bin2hex(random_bytes(32));
        $token = new EmailVerificationToken(
            $user,
            hash('sha256', $rawToken),
            new \DateTimeImmutable('-1 hour'),
        );
        $em->persist($token);
        $em->flush();

        $client->request(
            'POST',
            '/api/v1/auth/verify-email',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['token' => $rawToken], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('bad_request', $payload['error']['code']);
    }

    public function testReusedTokenIsRejected(): void
    {
        $client = self::createClient();
        $email = sprintf('reuse.%s@example.com', bin2hex(random_bytes(4)));
        $this->register($client, $email);
        $rawToken = $this->issueKnownToken($email);

        $body = json_encode(['token' => $rawToken], \JSON_THROW_ON_ERROR);

        $client->request('POST', '/api/v1/auth/verify-email', server: ['CONTENT_TYPE' => 'application/json'], content: $body);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request('POST', '/api/v1/auth/verify-email', server: ['CONTENT_TYPE' => 'application/json'], content: $body);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    private function register(KernelBrowser $client, string $email): void
    {
        $client->request(
            'POST',
            '/api/v1/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'fullName' => 'Verify User',
                'email' => $email,
                'password' => 'SecurePass1',
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    private function issueKnownToken(string $email): string
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);

        $rawToken = self::getContainer()
            ->get(EmailVerificationService::class)
            ->issueToken($user);
        $em->flush();

        return $rawToken;
    }
}
