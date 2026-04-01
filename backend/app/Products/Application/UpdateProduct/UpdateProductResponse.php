<?php

namespace App\Products\Application\UpdateProduct;

use App\Products\Domain\Entity\Product;

final readonly class UpdateProductResponse
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
        public int $restaurantId,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Product $product): self
    {
        return new self(
            id: $product->id()->value(),
            familyId: $product->familyId()->value(),
            taxId: $product->taxId()->value(),
            name: $product->name(),
            price: $product->price()->value(),
            stock: $product->stock()->value(),
            imageSrc: $product->imageSrc(),
            active: $product->status()->isActive(),
            restaurantId: $product->restaurantId(),
            createdAt: $product->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $product->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

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
            'restaurant_id' => $this->restaurantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}