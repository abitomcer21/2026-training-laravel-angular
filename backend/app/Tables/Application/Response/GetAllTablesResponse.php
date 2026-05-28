<?php

namespace App\Tables\Application\Response;

use App\Tables\Domain\Entity\Table;

final readonly class GetAllTablesResponse
{
    private function __construct(
        private array $tables,
        private int $total,
    ) {}

    public static function create(array $tables): self
    {
        $tablesData = array_map(
            static fn (Table $table): array => [
                'id'            => $table->id()->value(),
                'zone_id'       => $table->zoneId(),
                'name'          => $table->name(),
                'restaurant_id' => $table->restaurantId(),
                'created_at'    => $table->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at'    => $table->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $tables,
        );

        return new self(
            tables: $tablesData,
            total:  count($tablesData),
        );
    }

    public function toArray(): array
    {
        return [
            'tables' => $this->tables,
            'total'  => $this->total,
        ];
    }
}