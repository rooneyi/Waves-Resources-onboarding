<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HealthTest extends WebTestCase
{
    public function testHealthEndpointReturnsOk(): void
    {
        $client = self::createClient();
        $client->request('GET', '/health');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $payload = json_decode($client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('ok', $payload['status']);
        self::assertSame('ok', $payload['checks']['database']);
    }

    public function testApiDocIsPublic(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/doc.json');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
