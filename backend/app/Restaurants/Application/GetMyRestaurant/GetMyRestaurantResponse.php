<?php

namespace App\Restaurants\Application\GetMyRestaurant;

use App\Restaurants\Domain\Entity\Restaurant;

final readonly class GetMyRestaurantResponse
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

    public static function create(Restaurant $restaurant): self
    {
        return new self(
            id: $restaurant->id()->value(),
            name: $restaurant->name(),
            legalName: $restaurant->legalName(),
            taxId: $restaurant->taxId(),
            email: $restaurant->email()->value(),
            createdAt: $restaurant->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $restaurant->updatedAt()->format(\DateTimeInterface::ATOM),
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