<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Profile;

use App\Entity\ProfileImage;
use App\Entity\User;
use App\Repository\ProfileImageRepository;
use App\Service\Storage\ObjectStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProfileImageService
{
    private const MAX_BYTES = 2_097_152; // 2 MiB

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly ObjectStorageInterface $objectStorage,
        private readonly ProfileImageRepository $profileImageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{mimeType: string, size: int, objectKey: string}
     */
    public function upload(User $user, UploadedFile $file): array
    {
        $validated = $this->validateUploadedFile($file);
        $newKey = $this->buildObjectKey($user, $validated['extension']);

        $this->objectStorage->put($newKey, $validated['contents'], $validated['mimeType']);

        if (!$this->objectStorage->exists($newKey)) {
            throw new \RuntimeException('Failed to store profile image.');
        }

        $existing = $this->profileImageRepository->findOneByUser($user);
        $oldKey = $existing?->getObjectKey();

        if (null === $existing) {
            $existing = new ProfileImage($user, $newKey, $validated['mimeType'], $validated['size']);
            $this->entityManager->persist($existing);
        } else {
            $existing->setObjectKey($newKey);
            $existing->setMimeType($validated['mimeType']);
            $existing->setSize($validated['size']);
        }

        $this->entityManager->flush();

        if (null !== $oldKey && $oldKey !== $newKey) {
            $this->objectStorage->delete($oldKey);
        }

        return [
            'mimeType' => $existing->getMimeType(),
            'size' => $existing->getSize(),
            'objectKey' => $existing->getObjectKey(),
        ];
    }

    /**
     * @return array{contents: string, mimeType: string, size: int}
     */
    public function get(User $user): array
    {
        $image = $this->profileImageRepository->findOneByUser($user);

        if (null === $image) {
            throw new NotFoundHttpException('Profile image not found.');
        }

        $contents = $this->objectStorage->get($image->getObjectKey());

        return [
            'contents' => $contents,
            'mimeType' => $image->getMimeType(),
            'size' => $image->getSize(),
        ];
    }

    /**
     * @return array{contents: string, mimeType: string, size: int, extension: string}
     */
    private function validateUploadedFile(UploadedFile $file): array
    {
        if (!$file->isValid()) {
            throw new BadRequestHttpException('Invalid uploaded file.');
        }

        $size = $file->getSize();
        if (false === $size || $size <= 0 || $size > self::MAX_BYTES) {
            throw new BadRequestHttpException('Image must be between 1 byte and 2 MiB.');
        }

        $contents = file_get_contents($file->getPathname());
        if (false === $contents || '' === $contents) {
            throw new BadRequestHttpException('Unable to read uploaded file.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($contents) ?: '';

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new BadRequestHttpException('Only JPEG, PNG and WebP images are allowed.');
        }

        return [
            'contents' => $contents,
            'mimeType' => $mimeType,
            'size' => $size,
            'extension' => self::ALLOWED_MIME_TYPES[$mimeType],
        ];
    }

    private function buildObjectKey(User $user, string $extension): string
    {
        return sprintf(
            'profile-images/%d/%s.%s',
            (int) $user->getId(),
            bin2hex(random_bytes(16)),
            $extension,
        );
    }
}
