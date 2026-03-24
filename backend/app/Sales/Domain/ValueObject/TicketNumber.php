<?php

namespace App\Sales\Domain\ValueObject;

class TicketNumber
{
    private int $number;

    private function __construct(int $number)
    {
        if ($number < 1) {
            throw new \InvalidArgumentException('Ticket number must be greater than 0.');
        }
        $this->number = $number;
    }

    public static function create(int $number): self
    {
        return new self($number);
    }

    public function number(): int
    {
        return $this->number;
    }

    public function value(): int
    {
        return $this->number;
    }
}
