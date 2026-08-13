<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Controller\Api\V1\Profile;

use App\Entity\User;
use App\Service\Profile\ProfileImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class UploadProfileImageController extends AbstractController
{
    public function __construct(
        private readonly ProfileImageService $profileImageService,
    ) {
    }

    #[Route('/api/v1/me/profile-image', name: 'api_v1_me_profile_image_upload', methods: ['POST'])]
    public function __invoke(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $file = $request->files->get('image');

        if (!$file instanceof UploadedFile) {
            throw new BadRequestHttpException('Missing image file field "image".');
        }

        $result = $this->profileImageService->upload($user, $file);

        return $this->json([
            'mimeType' => $result['mimeType'],
            'size' => $result['size'],
        ], Response::HTTP_CREATED);
    }
}
