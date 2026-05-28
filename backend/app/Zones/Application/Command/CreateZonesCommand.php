<?php

namespace App\Zones\Application\Command;

use App\Zones\Domain\ValueObject\ZoneName;

final readonly class CreateZonesCommand
{
    private function __construct(
        public ZoneName $name,
        public int $restaurantId,
    ) {}

    public static function create(string $name, int $restaurantId): self
    {
        return new self(
            name:         ZoneName::create($name),
            restaurantId: $restaurantId,
        );
    }
}