<?php

namespace App\Zones\Application\DeleteZone;

use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class DeleteZones
{
    public function __construct(private ZonesRepositoryInterface $zonesRepository)
    {
    }

    public function __invoke(string $id): bool
    {
        if (!$this->zonesRepository->findById($id)) {
            return false;
        }

        $this->zonesRepository->delete($id);

        return true;
    }
}
