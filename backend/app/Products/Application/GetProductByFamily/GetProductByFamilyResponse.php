<?php

namespace App\Products\Application\GetProductByFamily;

class GetProductByFamilyResponse
{
    private function __construct(
        private array $products,
    ) {}

    public static function fromProducts(array $products): self
    {
        $data = array_map(fn ($product) => [
            'id' => $product->id()->value(),
            'name' => $product->name()->value(),
            'price' => $product->price()->value(),
            'stock' => $product->stock()->value(),
            'image_src' => $product->imageSrc()->value(),
            'active' => $product->status()->isActive(),
            'restaurant_id' => $product->restaurantId(),
            'tax_id' => $product->taxId(),
            'Family_id' => $product->FamilyId(),
            'created_at' => $product->createdAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $product->updatedAt()->format(\DateTimeInterface::ATOM),
        ], $products);

        return new self($data);
    }

    public function toArray(): array
    {
        return [
            'products' => $this->products,
        ];
    }
}
