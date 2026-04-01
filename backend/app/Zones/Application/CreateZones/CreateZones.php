<?php

namespace App\Zones\Application\CreateZones;

use App\Zones\Domain\Entity\Zones;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;
use App\Zones\Domain\ValueObject\ZoneName;

class CreateZones
{
    public function __construct(
        private ZonesRepositoryInterface $zonesRepository,
    ) {}

    public function __invoke(
        string $name,
        int $restaurantId,
    ): CreateZonesResponse {
        $nameVO = ZoneName::create($name);
        $zone = Zones::dddCreate($nameVO, $restaurantId);

        $this->zonesRepository->save($zone);

        return CreateZonesResponse::create($zone);
    }
}
