<?php

namespace App\Zones\Application\Handler;

use App\Zones\Application\Command\DeleteZonesCommand;
use App\Zones\Domain\Exceptions\ZoneNotFoundException;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class DeleteZonesHandler
{
    public function __construct(
        private ZonesRepositoryInterface $zonesRepository,
    ) {}

    public function __invoke(DeleteZonesCommand $command): void
    {
        $zone = $this->zonesRepository->findById($command->id->value());

        if ($zone === null) {
            throw new ZoneNotFoundException($command->id->value());
        }

        $this->zonesRepository->delete($command->id->value());
    }
}