<?php

namespace App\Zones\Domain\Exceptions;

class ZoneNotFoundException extends \DomainException
{
    public function __construct(string $id)
    {
        parent::__construct(
            sprintf('Zone with ID %s not found', $id),
            404,
        );
    }
}