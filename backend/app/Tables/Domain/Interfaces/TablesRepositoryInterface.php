<?php

namespace App\Tables\Domain\Interfaces;

use App\Tables\Domain\Entity\Table;

interface TablesRepositoryInterface
{
    public function save(Table $table): void;

    public function findById(string $id): ?Table;

    public function all(): array;

    public function delete(string $id): void;
}
