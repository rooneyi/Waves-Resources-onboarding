<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Controller\Api\V1\Auth;

use App\DTO\Auth\VerifyEmailRequest;
use App\Service\Authentication\VerifyEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class VerifyEmailController extends AbstractController
{
    public function __construct(
        private readonly VerifyEmailService $verifyEmailService,
    ) {
    }

    #[Route('/api/v1/auth/verify-email', name: 'api_v1_auth_verify_email', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] VerifyEmailRequest $request): JsonResponse
    {
        $user = $this->verifyEmailService->verify($request);

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'emailVerified' => $user->isEmailVerified(),
        ], Response::HTTP_OK);
    }
}
