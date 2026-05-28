<?php

namespace App\Tables\Application\Response;

use App\Tables\Domain\Entity\Table;

final readonly class GetTableByIdResponse
{
    private function __construct(
        private string $id,
        private string $name,
        private int $zoneId,
        private int $restaurantId,
        private string $createdAt,
        private string $updatedAt,
    ) {}

    public static function create(Table $table): self
    {
        return new self(
            id:           $table->id()->value(),
            name:         $table->name(),
            zoneId:       $table->zoneId(),
            restaurantId: $table->restaurantId(),
            createdAt:    $table->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt:    $table->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'zone_id'       => $this->zoneId,
            'name'          => $this->name,
            'restaurant_id' => $this->restaurantId,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}