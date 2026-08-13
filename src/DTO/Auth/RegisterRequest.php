<?php

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public readonly string $fullName = '',

        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public readonly string $email = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Za-z])(?=.*\d).+$/',
            message: 'Password must contain at least one letter and one number.',
        )]
        public readonly string $password = '',
    ) {
    }
}
