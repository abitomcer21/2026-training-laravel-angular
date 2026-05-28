<?php

namespace App\Zones\Application\Command;

use App\Shared\Domain\ValueObject\Uuid;

final readonly class DeleteZonesCommand
{
    public function __construct(
        public Uuid $id,
        public int $restaurantId,
    ) {}

    public static function create(string $id, int $restaurantId): self
    {
        return new self(
            id:           Uuid::create($id),
            restaurantId: $restaurantId,
        );
    }
}