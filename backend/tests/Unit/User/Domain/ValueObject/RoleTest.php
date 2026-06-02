<?php

namespace Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\ValueObject\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_create_valid_roles(): void
    {
        foreach (['admin', 'supervisor', 'camarero', 'chef'] as $role) {
            $this->assertSame($role, Role::create($role)->value());
        }
    }

    public function test_create_invalid_role_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Role::create('invalid');
    }

    public function test_static_factories(): void
    {
        $this->assertTrue(Role::admin()->isAdmin());
        $this->assertTrue(Role::supervisor()->isSupervisor());
        $this->assertTrue(Role::camarero()->isCamarero());
        $this->assertTrue(Role::chef()->isChef());
    }

    public function test_equals(): void
    {
        $this->assertTrue(Role::admin()->equals(Role::admin()));
        $this->assertFalse(Role::admin()->equals(Role::chef()));
    }
}