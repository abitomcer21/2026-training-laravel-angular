<?php

namespace App\Sales\Domain\ValueObject;

class SalesStatus
{
    private const OPEN = 'open';
    private const CLOSED = 'closed';

    private string $status;

    private function __construct(string $status)
    {
        if (!in_array($status, [self::OPEN, self::CLOSED])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid sales status. Must be one of: %s', implode(', ', [self::OPEN, self::CLOSED]))
            );
        }
        $this->status = $status;
    }

    public static function create(string $status): self
    {
        return new self($status);
    }

    public static function open(): self
    {
        return new self(self::OPEN);
    }

    public static function closed(): self
    {
        return new self(self::CLOSED);
    }

    public function isOpen(): bool
    {
        return $this->status === self::OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::CLOSED;
    }

    public function value(): string
    {
        return $this->status;
    }
}
