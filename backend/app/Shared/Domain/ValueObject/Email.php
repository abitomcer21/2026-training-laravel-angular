<?php

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exceptions\InvalidEmailException;

class Email
{
    private string $value;

    private function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
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