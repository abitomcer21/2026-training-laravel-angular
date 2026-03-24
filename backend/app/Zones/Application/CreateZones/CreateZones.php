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

    public function __invoke(string $name): CreateZonesResponse
    {
        $nameVO = ZoneName::create($name);
        $zones = Zones::dddCreate($nameVO);
        $this->zonesRepository->save($zones);

        return CreateZonesResponse::create($zones);
    }
}
