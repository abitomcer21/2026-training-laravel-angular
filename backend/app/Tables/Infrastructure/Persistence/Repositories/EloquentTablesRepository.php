<?php

namespace App\Tables\Infrastructure\Persistence\Repositories;

use App\Tables\Domain\Entity\Table;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;

class EloquentTablesRepository implements TablesRepositoryInterface
{
    public function __construct(
        private EloquentTables $model,
    ) {}

    public function save(Table $tables): void
    {
        $model = $this->model->newQuery()->firstOrNew(['uuid' => $tables->id()->value()]);

        if (! $model->exists) {
            $model->created_at = $tables->createdAt()->value();
        }

        $model->fill([
            'restaurant_id' => $tables->restaurantId(),
            'zone_id' => $tables->zoneId(),
            'name' => $tables->name(),
        ]);

        $model->updated_at = $tables->updatedAt()->value();

        $model->save();
    }

    public function findById(string $id): ?Table
    {
        $eloquentTable = $this->model->newQuery()->where('uuid', $id)->first();

        if (! $eloquentTable) {
            return null;
        }

        return Table::fromPersistence(
            $eloquentTable->uuid,
            $eloquentTable->zone_id,
            $eloquentTable->name,
            $eloquentTable->restaurant_id,
            $eloquentTable->created_at->toDateTimeImmutable(),
            $eloquentTable->updated_at->toDateTimeImmutable(),
        );
    }

    public function all(): array
    {
        return $this->model->newQuery()->get()->map(
            fn (EloquentTables $table): Table => Table::fromPersistence(
                $table->uuid,
                $table->zone_id,
                $table->name,
                $table->restaurant_id,
                $table->created_at->toDateTimeImmutable(),
                $table->updated_at->toDateTimeImmutable(),
            ),
        )->toArray();
    }

    public function delete(string $id): void
    {
        $this->model->newQuery()->where('uuid', $id)->delete();
    }
}
