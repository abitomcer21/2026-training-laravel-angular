<?php

namespace App\Zones\Application\Handler;

use App\Zones\Application\Query\GetAllZonesQuery;
use App\Zones\Application\Response\GetAllZonesResponse;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class GetAllZonesHandler
{
    public function __construct(
        private ZonesRepositoryInterface $zonesRepository,
    ) {}

    public function __invoke(GetAllZonesQuery $query): GetAllZonesResponse
    {
        $zones = $this->zonesRepository->findAllByRestaurant($query->restaurantId);

        return GetAllZonesResponse::create($zones);
    }
}