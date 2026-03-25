<?php

namespace App\Restaurants\Domain\ValueObject;

class RestaurantPassword
{
    private const MAX_LENGTH = 255;

    private string $value;

    private function __construct(string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('Restaurant password cannot be empty.');
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Restaurant password cannot exceed %d characters.', self::MAX_LENGTH)
            );
        }

        $this->value = $value;
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