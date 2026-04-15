<?php

namespace App\Restaurants\Application\CreateRestaurant;

use App\Restaurants\Domain\Entity\Restaurant;

final readonly class CreateRestaurantResponse
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
            $restaurant->id()->value(),
            $restaurant->name()->value(),
            $restaurant->legalName()->value(),
            $restaurant->taxId()->value(),
            $restaurant->email()->value(),
            $restaurant->createdAt()->format(\DateTimeInterface::ATOM),
            $restaurant->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

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