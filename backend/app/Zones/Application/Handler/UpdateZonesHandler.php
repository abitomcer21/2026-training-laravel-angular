<?php

namespace App\Zones\Application\Handler;

use App\Zones\Application\Command\UpdateZonesCommand;
use App\Zones\Application\Response\UpdateZonesResponse;
use App\Zones\Domain\Exceptions\ZoneNotFoundException;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class UpdateZonesHandler
{
    public function __construct(
        private ZonesRepositoryInterface $zonesRepository,
    ) {}

    public function __invoke(UpdateZonesCommand $command): UpdateZonesResponse
    {
        $zone = $this->zonesRepository->findById($command->id->value());

        if ($zone === null) {
            throw new ZoneNotFoundException($command->id->value());
        }

        $name = $command->name ?? $zone->name();

        $updatedZone = $zone->updateData($name);

        $this->zonesRepository->save($updatedZone);

        return UpdateZonesResponse::create($updatedZone);
    }
}