<?php

namespace App\Restaurants\Application\Response;

use App\Restaurants\Domain\Entity\Restaurant;

final readonly class GetMyRestaurantResponse
{
    private function __construct(
        private string $id,
        private string $name,
        private string $legalName,
        private string $taxId,
        private string $email,
        private string $createdAt,
        private string $updatedAt,
    ) {}

    public static function create(Restaurant $restaurant): self
    {
        return new self(
            id:        $restaurant->id()->value(),
            name:      $restaurant->name()->value(),
            legalName: $restaurant->legalName()->value(),
            taxId:     $restaurant->taxId()->value(),
            email:     $restaurant->email()->value(),
            createdAt: $restaurant->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $restaurant->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'legal_name' => $this->legalName,
            'tax_id'     => $this->taxId,
            'email'      => $this->email,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
