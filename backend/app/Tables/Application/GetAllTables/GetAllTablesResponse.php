<?php

namespace App\Tables\Application\GetAllTables;

use App\Tables\Domain\Entity\Table;

final readonly class GetAllTablesResponse
{
    public function __construct(
        public array $tables,
        public int $total,
    )
    {}

    public static function create(array $tables):self
    {
        $tablesData = array_map(
            static fn (Table $tables):array => [
                'id' => $tables->id()->value(),
                'zone_id' => $tables->zoneId(),
                'name'=> $tables->name(),
                'restaurant_id' => $tables->restaurantId(),
                'created_at' => $tables->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $tables->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $tables,
        );

        return new self(
            tables: $tablesData,
            total: count($tablesData),
        );
    }

    public function toArray(): array
    {
        return [
            'tables' => $this->tables,
            'total' => $this->total,
        ];
    }
}