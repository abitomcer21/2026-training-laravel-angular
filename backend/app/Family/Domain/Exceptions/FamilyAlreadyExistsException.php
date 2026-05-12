<?php

namespace App\Family\Domain\Exceptions;

class FamilyAlreadyExistsException extends \Exception
{
    public function __construct(string $name, int $restaurantId)
    {
        parent::__construct(
            sprintf('Family with name "%s" already exists in restaurant %d', $name, $restaurantId),
            409
        );
    }
}