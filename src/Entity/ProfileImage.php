<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Entity;

use App\Repository\ProfileImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileImageRepository::class)]
#[ORM\Table(name: 'profile_images')]
#[ORM\UniqueConstraint(name: 'uniq_profile_images_user', columns: ['user_id'])]
class ProfileImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(#[ORM\OneToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $user, #[ORM\Column(length: 255)]
        private string $objectKey, #[ORM\Column(length: 100)]
        private string $mimeType, #[ORM\Column]
        private int $size)
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getObjectKey(): string
    {
        return $this->objectKey;
    }

    public function setObjectKey(string $objectKey): void
    {
        $this->objectKey = $objectKey;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
