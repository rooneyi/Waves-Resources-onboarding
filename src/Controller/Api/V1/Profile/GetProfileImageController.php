<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Controller\Api\V1\Profile;

use App\Entity\User;
use App\Service\Profile\ProfileImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class GetProfileImageController extends AbstractController
{
    public function __construct(
        private readonly ProfileImageService $profileImageService,
    ) {
    }

    #[Route('/api/v1/me/profile-image', name: 'api_v1_me_profile_image_get', methods: ['GET'])]
    public function __invoke(#[CurrentUser] User $user): Response
    {
        $image = $this->profileImageService->get($user);

        return new Response($image['contents'], Response::HTTP_OK, [
            'Content-Type' => $image['mimeType'],
            'Content-Length' => (string) strlen($image['contents']),
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
