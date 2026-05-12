<?php

namespace App\Family\Domain\ValueObject;

use App\Family\Domain\Exceptions\EmptyFamilyNameException;
use App\Family\Domain\Exceptions\FamilyNameTooLongException;

class FamilyName
{
    private const MAX_LENGTH = 255;

    private string $value;

    private function __construct(string $value)
    {
        $trimmed = trim($value);
        
        if ($trimmed === '') {
            throw new EmptyFamilyNameException();
        }
        
        $length = mb_strlen($trimmed);
        if ($length > self::MAX_LENGTH) {
            throw new FamilyNameTooLongException(self::MAX_LENGTH, $length);
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