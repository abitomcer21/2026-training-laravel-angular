<?php

namespace App\Restaurants\Domain\ValueObject;

class RestaurantPassword
{
    private const MIN_LENGTH = 8;
    private const MAX_LENGTH = 255;

    private string $value;

    private function __construct(string $value)
    {
        $trimmed = $value;

        if ($trimmed === '') {
            throw new \InvalidArgumentException('Restaurant password cannot be empty.');
        }

        $length = mb_strlen($trimmed);
        if ($length < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Restaurant password must be at least %d characters.', self::MIN_LENGTH)
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Restaurant password cannot exceed %d characters.', self::MAX_LENGTH)
            );
        }

        if (!preg_match('/[A-Z]/', $trimmed)) {
            throw new \InvalidArgumentException('Restaurant password must contain at least one uppercase letter.');
        }
        if (!preg_match('/[a-z]/', $trimmed)) {
            throw new \InvalidArgumentException('Restaurant password must contain at least one lowercase letter.');
        }
        if (!preg_match('/[0-9]/', $trimmed)) {
            throw new \InvalidArgumentException('Restaurant password must contain at least one number.');
        }
        if (!preg_match('/[^A-Za-z0-9]/', $trimmed)) {
            throw new \InvalidArgumentException('Restaurant password must contain at least one special character.');
        }

        $this->value = $trimmed;
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}