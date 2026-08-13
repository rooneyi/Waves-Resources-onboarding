<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Controller\Api\V1\Auth;

use App\DTO\Auth\RefreshTokenRequest;
use App\Service\Authentication\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class RefreshTokenController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    #[Route('/api/v1/auth/refresh', name: 'api_v1_auth_refresh', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] RefreshTokenRequest $request): JsonResponse
    {
        return $this->json($this->authenticationService->refresh($request), Response::HTTP_OK);
    }
}
