<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Tests\Functional\Auth;

use Symfony\Component\HttpFoundation\Response;

final class LoginTest extends AuthTestCase
{
    public function testValidLoginReturnsTokenPair(): void
    {
        $client = static::createClient();
        $email = sprintf('login.%s@example.com', bin2hex(random_bytes(4)));
        $this->registerVerifiedUser($client, $email);

        $client->request(
            'POST',
            '/api/v1/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'SecurePass1',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Bearer', $payload['tokenType']);
        self::assertSame(900, $payload['expiresIn']);
        self::assertNotEmpty($payload['accessToken']);
        self::assertNotEmpty($payload['refreshToken']);
        self::assertSame(64, strlen($payload['refreshToken']));
    }

    public function testInvalidCredentialsReturnUnauthorized(): void
    {
        $client = static::createClient();
        $email = sprintf('badlogin.%s@example.com', bin2hex(random_bytes(4)));
        $this->registerVerifiedUser($client, $email);

        $client->request(
            'POST',
            '/api/v1/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'WrongPass1',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('unauthorized', $payload['error']['code']);
        self::assertSame('Invalid credentials.', $payload['error']['message']);
    }

    public function testUnverifiedUserCannotLogin(): void
    {
        $client = static::createClient();
        $email = sprintf('unverified.%s@example.com', bin2hex(random_bytes(4)));

        $client->request(
            'POST',
            '/api/v1/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'fullName' => 'Unverified User',
                'email' => $email,
                'password' => 'SecurePass1',
            ], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request(
            'POST',
            '/api/v1/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'SecurePass1',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRefreshAndLogout(): void
    {
        $client = static::createClient();
        $email = sprintf('refresh.%s@example.com', bin2hex(random_bytes(4)));
        $this->registerVerifiedUser($client, $email);

        $client->request(
            'POST',
            '/api/v1/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'SecurePass1',
            ], JSON_THROW_ON_ERROR),
        );
        $login = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            '/api/v1/auth/refresh',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['refreshToken' => $login['refreshToken']], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $refreshed = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotSame($login['refreshToken'], $refreshed['refreshToken']);

        $client->request(
            'POST',
            '/api/v1/auth/logout',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['refreshToken' => $refreshed['refreshToken']], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request(
            'POST',
            '/api/v1/auth/refresh',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['refreshToken' => $refreshed['refreshToken']], JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
