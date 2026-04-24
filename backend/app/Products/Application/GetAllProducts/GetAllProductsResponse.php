<?php

namespace App\Products\Application\GetAllProducts;

use App\Products\Domain\Entity\Product;

final readonly class GetAllProductsResponse
{
    public function __construct(
        public array $products,
        public int $total,
    ) {}

    public static function create(array $products): self
    {
        $productsData = array_map(
            static fn (Product $product): array => [
                'id' => $product->id()->value(),
                'family_id' => $product->familyId()->value(),
                'tax_id' => $product->taxId(),
                'name' => $product->name()->value(),
                'price' => $product->price()->value(),
                'stock' => $product->stock()->value(),
                'image_src' => $product->imageSrc()->value(),
                'active' => $product->status()->isActive(),
                'restaurant_id' => $product->restaurantId(),
                'created_at' => $product->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $product->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $products,
        );

        return new self(
            products: $productsData,
            total: count($productsData),
        );
    }

    public function toArray(): array
    {
        return [
            'products' => $this->products,
            'total' => $this->total,
        ];
    }
}
