<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Controller\Api\V1\Profile;

use App\DTO\Profile\UpdateProfileRequest;
use App\Entity\User;
use App\Service\Profile\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class UpdateProfileController extends AbstractController
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {
    }

    #[Route('/api/v1/me', name: 'api_v1_me_patch', methods: ['PATCH'])]
    public function __invoke(
        #[CurrentUser] User $user,
        #[MapRequestPayload] UpdateProfileRequest $request,
    ): JsonResponse {
        return $this->json($this->profileService->updateProfile($user, $request), Response::HTTP_OK);
    }
}
