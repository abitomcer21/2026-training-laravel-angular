<?php

namespace App\zones\application\deletezones;

use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;

class DeleteZones
{
    public function __construct(private ZonesRepositoryInterface $zonesRepository)
    {}

    public function __invoke(string $id): bool
    {
        $zones = $this->zonesRepository->findById($id);

        if(!$zones){
            return false;
        }

        $zones->markAsDeleted();
        $this->zonesRepository->save($zones);

        return true;
    }
}