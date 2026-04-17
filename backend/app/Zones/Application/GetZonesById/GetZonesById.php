<?php

namespace App\Zones\Application\GetZonesById;

use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class GetZonesById
{
    public function __construct(
        private ZonesRepositoryInterface $zonesRepository,
    ) {}

    public function __invoke(string $id): ?GetZonesByIdResponse
    {
        $zones = $this->zonesRepository->findById($id);

        if (! $zones) {
            return null;
        }

        return GetZonesByIdResponse::create($zones);
    }
}
