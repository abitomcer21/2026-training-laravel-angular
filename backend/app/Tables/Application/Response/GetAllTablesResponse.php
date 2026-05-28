<?php

namespace App\Tables\Application\Response;

final readonly class GetAllTablesResponse
{
    private function __construct(
        private array $tables,
        private int $total,
    ) {}

    public static function create(array $tables): self
    {
        $tablesData = array_map(
            static fn (array $item): array => [
                'id'            => $item['table']->id()->value(),
                'zone_id'       => $item['table']->zoneId(),
                'zone_uuid'     => $item['zone_uuid'],
                'name'          => $item['table']->name(),
                'restaurant_id' => $item['table']->restaurantId(),
                'created_at'    => $item['table']->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at'    => $item['table']->updatedAt()->format(\DateTimeInterface::ATOM),
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