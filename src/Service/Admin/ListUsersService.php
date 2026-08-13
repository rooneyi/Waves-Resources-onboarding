<?php

declare(strict_types=1);

/**
 * @author rooneyi <22ki129@esisalama.org>
 */

namespace App\Service\Admin;

use App\DTO\Admin\ListUsersQuery;
use App\Repository\UserRepository;

final class ListUsersService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array{page: int, limit: int, total: int, pages: int}}
     */
    public function list(ListUsersQuery $query): array
    {
        $result = $this->userRepository->searchAdminUsers(
            page: $query->page,
            limit: $query->limit,
            role: $query->role,
            verified: $query->verified,
            sort: $query->sort,
            direction: $query->direction,
        );

        $data = [];
        foreach ($result['items'] as $user) {
            $data[] = [
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'emailVerified' => $user->isEmailVerified(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        $pages = (int) max(1, (int) ceil($result['total'] / $query->limit));

        return [
            'data' => $data,
            'meta' => [
                'page' => $query->page,
                'limit' => $query->limit,
                'total' => $result['total'],
                'pages' => $pages,
            ],
        ];
    }
}
