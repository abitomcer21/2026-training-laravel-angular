<?php

namespace App\Zones\Application\GetAllZones;

use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class GetAllZones
{
    public function __construct(private ZonesRepositoryInterface $zonesRepository) {}

    public function __invoke(): GetAllZonesResponse
    {
        $zones = $this->zonesRepository->all();

        return GetAllZonesResponse::create($zones);
    }
}
