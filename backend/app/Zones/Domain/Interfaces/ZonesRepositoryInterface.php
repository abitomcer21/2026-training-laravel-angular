<?php

namespace App\Zones\Domain\Interfaces;

use App\Zones\Domain\Entity\Zones;

interface ZonesRepositoryInterface
{
    public function save(Zones $zones): void;

    public function findById(string $id): ?Zones;

    public function findByIdWithDatabaseId(string $id): ?array;

    public function all(): array;

    public function delete(string $id): void;
}
