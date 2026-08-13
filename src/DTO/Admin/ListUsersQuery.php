<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\DTO\Admin;

use Symfony\Component\Validator\Constraints as Assert;

final class ListUsersQuery
{
    public function __construct(
        #[Assert\Positive]
        public readonly int $page = 1,

        #[Assert\Range(min: 1, max: 100)]
        public readonly int $limit = 20,

        #[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'])]
        public readonly ?string $role = null,

        public readonly ?bool $verified = null,

        #[Assert\Choice(choices: ['createdAt', 'fullName'])]
        public readonly string $sort = 'createdAt',

        #[Assert\Choice(choices: ['asc', 'desc'])]
        public readonly string $direction = 'desc',
    ) {
    }
}
