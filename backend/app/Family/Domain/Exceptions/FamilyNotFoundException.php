<?php

namespace App\Family\Domain\Exceptions;

class FamilyNotFoundException extends \Exception
{
    private string $familyId;

    public function __construct(string $familyId)
    {
        parent::__construct(
            sprintf('Family with ID %s not found', $familyId),
            404
        );
        
        $this->familyId = $familyId;
    }

    public function getFamilyId(): string
    {
        return $this->familyId;
    }
}