<?php

namespace App\Zones\Application\UpdateZones;

use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;
use App\Zones\Domain\ValueObject\ZoneName;

class UpdateZones
{
    public function __construct(private ZonesRepositoryInterface $zonesRepository) {}

    public function __invoke(string $id, string $name): ?UpdateZonesResponse
    {
        $zones = $this->zonesRepository->findById($id);

        if (! $zones) {
            return null;
        }

        $zonesVO = ZoneName::create($name);
        $zone = $zones->updateData($zonesVO);
        $this->zonesRepository->save($zone);

        return UpdateZonesResponse::create($zone);
    }
}
