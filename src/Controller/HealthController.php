<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class HealthController
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $databaseOk = false;

        try {
            $this->connection->executeQuery('SELECT 1');
            $databaseOk = true;
        } catch (\Throwable) {
            $databaseOk = false;
        }

        $status = $databaseOk ? 'ok' : 'degraded';
        $httpStatus = $databaseOk ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse([
            'status' => $status,
            'checks' => [
                'database' => $databaseOk ? 'ok' : 'fail',
            ],
        ], $httpStatus);
    }
}
