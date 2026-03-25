<?php

namespace App\Restaurants\Application\CreateRestaurantes;

use App\Restaurants\Domain\Entity\Restaurants;

final readonly class CreateRestaurantesResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $legalName,
        public string $taxId,
        public string $email,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Restaurants $restaurants): self
    {
        return new self(
            id: $restaurants->id()->value(),
            name: $restaurants->name(),
            legalName: $restaurants->legalName(),
            taxId: $restaurants->taxId(),
            email: $restaurants->email(),
            createdAt: $restaurants->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $restaurants->updatedAt()->format(\DateTimeInterface::ATOM),
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
            'legal_name' => $this->legalName,
            'tax_id' => $this->taxId,
            'email' => $this->email,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}