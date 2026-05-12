<?php

namespace App\Shared\Domain\Exceptions;

class InvalidTaxPercentageException extends \Exception
{
    public function __construct(int $percentage)
    {
        parent::__construct(
            sprintf('Tax percentage must be between 0 and 100. Got: %d', $percentage),
            422
        );
    }
}