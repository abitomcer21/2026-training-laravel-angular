<?php

namespace App\Zones\Application\Handler;

use App\Zones\Application\Query\GetZonesByIdQuery;
use App\Zones\Application\Response\GetZonesByIdResponse;
use App\Zones\Domain\Exceptions\ZoneNotFoundException;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class GetZonesByIdHandler
{
    public function __construct(
        private ZonesRepositoryInterface $zonesRepository,
    ) {}

    public function __invoke(GetZonesByIdQuery $query): GetZonesByIdResponse
    {
        $zone = $this->zonesRepository->findById($query->id);

        if ($zone === null) {
            throw new ZoneNotFoundException($query->id);
        }

        return GetZonesByIdResponse::create($zone);
    }
}