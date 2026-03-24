<?php

namespace App\Taxes\Application\CreateTaxes;

use App\Taxes\Domain\Entity\Taxes;

final readonly class CreateTaxesResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $percentage,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Taxes $taxes): self
    {
        return new self(
            id: $taxes->id()->value(),
            name: $taxes->name(),
            percentage: $taxes->percentage()->value(),
            createdAt: $taxes->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $taxes->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'percentage' => $this->percentage,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
