<?php

namespace App\Family\Domain\Exceptions;

class FamilyAlreadyExistsException extends \DomainException
{
    public function __construct(string $name, int $restaurantId)
    {
        parent::__construct(sprintf('Family "%s" already exists in restaurant %d', $name, $restaurantId));
    }
}