<?php

namespace App\Order\Domain\ValueObject;

class OrderStatus
{
    public const OPEN = 'open';
    public const CLOSED = 'closed';
    public const CANCELLED = 'cancelled';

    private const VALID = [
        self::OPEN,
        self::CLOSED,
        self::CANCELLED,
    ];

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, self::VALID, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid order status "%s". Valid values: %s.', $value, implode(', ', self::VALID))
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
