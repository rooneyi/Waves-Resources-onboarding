<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Controller\Api\V1\Admin;

use App\DTO\Admin\ListUsersQuery;
use App\Enum\Role;
use App\Service\Admin\ListUsersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ListUsersController extends AbstractController
{
    public function __construct(
        private readonly ListUsersService $listUsersService,
    ) {
    }

    #[Route('/api/v1/admin/users', name: 'api_v1_admin_users', methods: ['GET'])]
    #[IsGranted(Role::Admin->value)]
    public function __invoke(#[MapQueryString] ListUsersQuery $query): JsonResponse
    {
        return $this->json($this->listUsersService->list($query), Response::HTTP_OK);
    }
}
