<?php 

namespace App\Families\Application\GetAllFamily;

use App\Families\Domain\Entity\Family;

final readonly class GetAllFamilyResponse
{
    public function __construct(
        public array $families,
        public int $total,
    ) {}

    public static function create(array $familiesEntities): self
    {
        $families = array_map(
            fn (Family $family) => [
                'id' => $family->id()->value(),
                'name' => $family->name(),
                'created_at' => $family->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $family->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $familiesEntities
        );

        return new self(
            families: $families,
            total: count($families),
        );
    }
}
