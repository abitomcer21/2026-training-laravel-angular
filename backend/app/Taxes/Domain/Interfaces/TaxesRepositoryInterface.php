<?php

namespace App\Taxes\Domain\Interfaces;

use App\Taxes\Domain\Entity\Taxes;

interface TaxesRepositoryInterface
{
    public function save(Taxes $taxes): void;

    public function findById(string $id): ?Taxes;

    public function all(): array;

    public function delete(string $id): void;
}
