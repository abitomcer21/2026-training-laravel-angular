<?php

namespace App\Shared\Domain\Exceptions;

class InvalidUuidException extends \Exception
{
    public function __construct(string $uuid)
    {
        parent::__construct(sprintf('Invalid UUID format: %s', $uuid), 422);
    }
}