<?php

namespace App\Zones\Application\Handler;

use App\Zones\Application\Command\CreateZonesCommand;
use App\Zones\Application\Response\CreateZonesResponse;
use App\Zones\Domain\Entity\Zones;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class CreateZonesHandler
{
    public function __construct(
        private ZonesRepositoryInterface $zonesRepository,
    ) {}

    public function __invoke(CreateZonesCommand $command): CreateZonesResponse
    {
        $zone = Zones::dddCreate($command->name, $command->restaurantId);

        $this->zonesRepository->save($zone);

        return CreateZonesResponse::create($zone);
    }
}