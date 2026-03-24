<?php

namespace App\Families\Domain\Interfaces;

use App\Families\Domain\Entity\Families;

interface FamiliesRepositoryInterface
{
    public function save(Families $families): void;

    public function findById(string $id): ?Families;
}
