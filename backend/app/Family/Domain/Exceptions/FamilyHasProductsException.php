<?php

namespace App\Family\Domain\Exceptions;

class FamilyHasProductsException extends \Exception
{
    public function __construct(string $familyId, int $productCount)
    {
        parent::__construct(
            sprintf('Cannot delete family %s because it has %d active products', $familyId, $productCount),
            409
        );
    }
}