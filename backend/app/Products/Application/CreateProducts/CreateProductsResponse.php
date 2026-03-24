<?php

namespace App\Products\Application\CreateProducts;

use App\Products\Domain\Entity\Products;

final readonly class CreateProductsResponse
{
    public function __construct(
        public string $id,
        public string $familyId,
        public string $taxId,
        public string $name,
        public int $price,
        public int $stock,
        public string $imageSrc,
        public bool $active,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Products $products): self
    {
        return new self(
            id: $products->id()->value(),
            familyId: $products->familyId()->value(),
            taxId: $products->taxId()->value(),
            name: $products->name(),
            price: $products->price()->value(),
            stock: $products->stock()->value(),
            imageSrc: $products->imageSrc(),
            active: $products->status()->isActive(),
            createdAt: $products->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $products->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'family_id' => $this->familyId,
            'tax_id' => $this->taxId,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_src' => $this->imageSrc,
            'active' => $this->active,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
