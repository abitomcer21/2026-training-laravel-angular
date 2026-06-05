<?php

namespace App\Tax\Domain\Exceptions;

class TaxNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Tax not found: {$id}");
    }
}