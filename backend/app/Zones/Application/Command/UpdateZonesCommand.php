<?php

namespace App\Zones\Application\Command;

use App\Zones\Domain\ValueObject\ZoneName;
use App\Shared\Domain\ValueObject\Uuid;

final readonly class UpdateZonesCommand
{
    private function __construct(
        public Uuid $id,
        public ?ZoneName $name,
        public int $restaurantId,
    ) {}

    public static function create(string $id, ?string $name, int $restaurantId): self
    {
        return new self(
            id:           Uuid::create($id),
            name:         $name !== null ? ZoneName::create($name) : null,
            restaurantId: $restaurantId,
        );
    }
}