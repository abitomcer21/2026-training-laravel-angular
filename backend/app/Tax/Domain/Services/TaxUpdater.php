<?php

namespace App\Tax\Domain\Services;

use App\Tax\Domain\Entity\Tax;
use App\Tax\Domain\ValueObject\TaxName;
use App\Tax\Domain\ValueObject\TaxPercentage;

class TaxUpdater
{
    public function update(Tax $tax, ?string $name, ?int $percentage): Tax
    {
        $newName       = $name !== null ? TaxName::create($name) : $tax->nameVO();
        $newPercentage = $percentage !== null ? TaxPercentage::create($percentage) : $tax->percentage();

        return $tax->updateData($newName, $newPercentage);
    }
}