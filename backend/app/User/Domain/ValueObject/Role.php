<?php

namespace App\User\Domain\ValueObject;

class Role
{
    public const ADMIN = 'admin';
    public const SUPERVISOR = 'supervisor';
    public const CAMARERO = 'camarero';
    public const CHEF = 'chef';

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, [self::ADMIN,self::SUPERVISOR, self::CAMARERO, self::CHEF], true)) {
            throw new \InvalidArgumentException('Invalid role value: ' . $value);
        }
        $this->value = $value;
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function supervisor():self
    {
        return new self(self::SUPERVISOR);
    }

    public static function camarero(): self
    {
        return new self(self::CAMARERO);
    }


    public static function chef(): self
    {
        return new self(self::CHEF);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Role $other): bool
    {
        return $this->value === $other->value();
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isSupervisor():bool
    {
        return $this->value === self::SUPERVISOR;
    }

    public function isCamarero(): bool
    {
        return $this->value === self::CAMARERO;
    }

    public function isChef(): bool
    {
        return $this->value === self::CHEF;
    }
}
