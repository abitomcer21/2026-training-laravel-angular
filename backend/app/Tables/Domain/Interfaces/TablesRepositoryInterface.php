<?php

namespace App\Tables\Domain\Interfaces;

use App\Tables\Domain\Entity\Tables;

interface TablesRepositoryInterface
{
    public function save(Tables $tables): void;

    public function findById(string $id): ?Tables;
}
