<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VerifyEmailRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 64, max: 64)]
        #[Assert\Regex(pattern: '/^[a-f0-9]+$/')]
        public string $token = '',
    ) {
    }
}
