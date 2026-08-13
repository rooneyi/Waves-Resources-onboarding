<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\DTO\Profile;

use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $currentPassword = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Za-z])(?=.*\d).+$/',
            message: 'Password must contain at least one letter and one number.',
        )]
        public readonly string $newPassword = '',
    ) {
    }
}
