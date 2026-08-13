<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Tests\Functional\Profile;

use App\Entity\User;
use App\Enum\Role;
use App\Tests\Functional\Auth\AuthTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class ProfileTest extends AuthTestCase
{
    public function testGetAndUpdateOwnProfile(): void
    {
        $client = self::createClient();
        $email = sprintf('me.%s@example.com', bin2hex(random_bytes(4)));
        $token = $this->login($client, $email);

        $client->request('GET', '/api/v1/me', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $profile = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame($email, $profile['email']);

        $client->request(
            'PATCH',
            '/api/v1/me',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            content: json_encode(['fullName' => 'Updated Name'], \JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $updated = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('Updated Name', $updated['fullName']);
        self::assertSame($email, $updated['email']);
    }

    public function testChangePasswordRequiresCurrentPassword(): void
    {
        $client = self::createClient();
        $email = sprintf('pwd.%s@example.com', bin2hex(random_bytes(4)));
        $token = $this->login($client, $email);

        $client->request(
            'POST',
            '/api/v1/me/password',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            content: json_encode([
                'currentPassword' => 'WrongPass1',
                'newPassword' => 'NewSecure1',
            ], \JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $client->request(
            'POST',
            '/api/v1/me/password',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            content: json_encode([
                'currentPassword' => 'SecurePass1',
                'newPassword' => 'NewSecure1',
            ], \JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request(
            'POST',
            '/api/v1/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'NewSecure1',
            ], \JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testUnauthenticatedMeIsUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/v1/me');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUserCannotAccessAdminUsers(): void
    {
        $client = self::createClient();
        $email = sprintf('user.%s@example.com', bin2hex(random_bytes(4)));
        $token = $this->login($client, $email);

        $client->request('GET', '/api/v1/admin/users', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanListUsers(): void
    {
        $client = self::createClient();
        $email = sprintf('admin.%s@example.com', bin2hex(random_bytes(4)));
        $this->login($client, $email);

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user);
        $user->setRoles([Role::Admin->value, Role::User->value]);
        $em->flush();

        // Re-login so JWT contains ROLE_ADMIN
        $client->request(
            'POST',
            '/api/v1/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'SecurePass1',
            ], \JSON_THROW_ON_ERROR),
        );
        $login = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $client->request('GET', '/api/v1/admin/users?page=1&limit=10', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$login['accessToken'],
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('data', $payload);
        self::assertArrayHasKey('meta', $payload);
        self::assertGreaterThanOrEqual(1, $payload['meta']['total']);
    }

    private function login(KernelBrowser $client, string $email): string
    {
        $this->registerVerifiedUser($client, $email);

        $client->request(
            'POST',
            '/api/v1/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => 'SecurePass1',
            ], \JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return $payload['accessToken'];
    }
}
