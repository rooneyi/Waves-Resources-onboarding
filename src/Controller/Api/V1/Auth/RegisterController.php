<?php

namespace App\Controller\Api\V1\Auth;

use App\DTO\Auth\RegisterRequest;
use App\Service\Authentication\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
    ) {
    }

    #[Route('/api/v1/auth/register', name: 'api_v1_auth_register', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] RegisterRequest $request): JsonResponse
    {
        $user = $this->registrationService->register($request);

        return $this->json([
            'id' => $user->getId(),
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'emailVerified' => $user->isEmailVerified(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], Response::HTTP_CREATED);
    }
}
