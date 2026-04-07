<?php

namespace App\Products\Application\CreateProduct;

use App\Products\Domain\Entity\Product;

final readonly class CreateProductResponse
{
    public function __construct(
        public string $id,
        public int $familyId,
        public int $taxId,
        public string $name,
        public int $price,
        public int $stock,
        public string $imageSrc,
        public bool $active,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Product $product): self
    {
        return new self(
            id: $product->id()->value(),
            familyId: $product->familyId(),
            taxId: $product->taxId(),
            name: $product->name(),
            price: $product->price()->value(),
            stock: $product->stock()->value(),
            imageSrc: $product->imageSrc(),
            active: $product->status()->isActive(),
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
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
