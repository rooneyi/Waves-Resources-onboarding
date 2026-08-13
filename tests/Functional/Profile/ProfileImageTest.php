<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Tests\Functional\Profile;

use App\Service\Storage\InMemoryObjectStorage;
use App\Service\Storage\ObjectStorageInterface;
use App\Tests\Functional\Auth\AuthTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

final class ProfileImageTest extends AuthTestCase
{
    public function testUploadReplaceAndGetProfileImage(): void
    {
        $client = self::createClient();
        $client->disableReboot();
        $email = sprintf('avatar.%s@example.com', bin2hex(random_bytes(4)));
        $token = $this->login($client, $email);

        $pngPath = $this->createTempPng();
        $upload = new UploadedFile($pngPath, 'avatar.png', 'image/png', null, true);

        $client->request(
            'POST',
            '/api/v1/me/profile-image',
            files: ['image' => $upload],
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
        );
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('GET', '/api/v1/me/profile-image', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('content-type', 'image/png');
        self::assertNotEmpty($client->getResponse()->getContent());

        $storage = self::getContainer()->get(ObjectStorageInterface::class);
        self::assertInstanceOf(InMemoryObjectStorage::class, $storage);

        $replacementPath = $this->createTempPng();
        $replacement = new UploadedFile($replacementPath, 'avatar2.png', 'image/png', null, true);
        $client->request(
            'POST',
            '/api/v1/me/profile-image',
            files: ['image' => $replacement],
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
        );
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('GET', '/api/v1/me/profile-image', server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testRejectsNonImageUpload(): void
    {
        $client = self::createClient();
        $email = sprintf('badimg.%s@example.com', bin2hex(random_bytes(4)));
        $token = $this->login($client, $email);

        $tmp = tempnam(sys_get_temp_dir(), 'txt');
        self::assertNotFalse($tmp);
        file_put_contents($tmp, 'not-an-image');
        $file = new UploadedFile($tmp, 'notes.txt', 'text/plain', null, true);

        $client->request(
            'POST',
            '/api/v1/me/profile-image',
            files: ['image' => $file],
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
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

    private function createTempPng(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'png');
        self::assertNotFalse($path);
        // 1x1 PNG
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
        self::assertNotFalse($png);
        file_put_contents($path, $png);

        return $path;
    }
}
