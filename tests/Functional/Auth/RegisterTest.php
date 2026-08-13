<?php

namespace App\Tests\Functional\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RegisterTest extends WebTestCase
{
    public function testValidRegistrationReturnsCreatedUser(): void
    {
        $client = static::createClient();
        $email = sprintf('ada.%s@example.com', bin2hex(random_bytes(4)));

        $client->request(
            'POST',
            '/api/v1/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'fullName' => 'Ada Lovelace',
                'email' => $email,
                'password' => 'SecurePass1',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Ada Lovelace', $payload['fullName']);
        self::assertSame($email, $payload['email']);
        self::assertFalse($payload['emailVerified']);
        self::assertArrayHasKey('id', $payload);
        self::assertArrayHasKey('createdAt', $payload);
        self::assertArrayNotHasKey('password', $payload);
    }

    public function testDuplicateEmailReturnsConflict(): void
    {
        $client = static::createClient();
        $email = sprintf('dup.%s@example.com', bin2hex(random_bytes(4)));
        $body = json_encode([
            'fullName' => 'First User',
            'email' => $email,
            'password' => 'SecurePass1',
        ], JSON_THROW_ON_ERROR);

        $client->request('POST', '/api/v1/auth/register', server: ['CONTENT_TYPE' => 'application/json'], content: $body);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('POST', '/api/v1/auth/register', server: ['CONTENT_TYPE' => 'application/json'], content: $body);
        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('conflict', $payload['error']['code']);
    }

    public function testInvalidEmailReturnsValidationError(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'fullName' => 'Bad Email',
                'email' => 'not-an-email',
                'password' => 'SecurePass1',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('validation_failed', $payload['error']['code']);
    }

    public function testWeakPasswordReturnsValidationError(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'fullName' => 'Weak Password',
                'email' => sprintf('weak.%s@example.com', bin2hex(random_bytes(4))),
                'password' => 'short',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('validation_failed', $payload['error']['code']);
    }
}
