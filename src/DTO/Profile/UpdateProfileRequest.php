<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\DTO\Profile;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateProfileRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $fullName = '',
    ) {
    }
}
