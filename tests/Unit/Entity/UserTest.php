<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Enum\Role;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testNewUserHasDefaultRoleAndUnverifiedEmail(): void
    {
        $user = new User();
        $user->setFullName('Ada Lovelace');
        $user->setEmail('Ada@Example.com');
        $user->setPassword('hashed-password');

        self::assertSame('ada@example.com', $user->getEmail());
        self::assertSame('Ada Lovelace', $user->getFullName());
        self::assertFalse($user->isEmailVerified());
        self::assertContains(Role::User->value, $user->getRoles());
        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();
        $user->setRoles([Role::Admin->value]);

        self::assertSame(
            [Role::Admin->value, Role::User->value],
            $user->getRoles()
        );
    }
}
