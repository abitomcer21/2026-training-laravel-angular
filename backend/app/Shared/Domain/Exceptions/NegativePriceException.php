<?php

namespace App\Shared\Domain\Exceptions;

class NegativePriceException extends \Exception
{
    public function __construct(int $cents)
    {
        parent::__construct(sprintf('Price cannot be negative. Got: %d cents', $cents), 422);
    }
}