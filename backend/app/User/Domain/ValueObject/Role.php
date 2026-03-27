<?php

namespace App\User\Domain\ValueObject;

class Role
{
    public const ADMIN = 'admin';
    public const CASHIER = 'cashier';
    public const WAITER = 'waiter';
    public const CHEF = 'chef';

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, [self::ADMIN, self::CASHIER, self::WAITER, self::CHEF], true)) {
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

    public static function cashier(): self
    {
        return new self(self::CASHIER);
    }

    public static function waiter(): self
    {
        return new self(self::WAITER);
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

    public function isCashier(): bool
    {
        return $this->value === self::CASHIER;
    }

    public function isWaiter(): bool
    {
        return $this->value === self::WAITER;
    }

    public function isChef(): bool
    {
        return $this->value === self::CHEF;
    }
}