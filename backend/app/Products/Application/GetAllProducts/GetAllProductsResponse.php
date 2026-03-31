<?php

namespace App\Products\Application\GetAllProducts;

use App\Products\Domain\Entity\Product;

final readonly class GetAllProductsResponse
{
    public function __construct(
        public array $products,
        public int $total,
    ) {}

    public static function create(array $productEntities): self
    {
        $products = array_map(
            fn (Product $product) => [
                'id' => $product->id()->value(),
                'family_id' => $product->familyId()->value(),
                'tax_id' => $product->taxId()->value(),
                'name' => $product->name(),
                'price' => $product->price()->value(),
                'stock' => $product->stock()->value(),
                'image_src' => $product->imageSrc(),
                'active' => $product->status()->isActive(),
                'restaurant_id' => $product->restaurantId(),
                'created_at' => $product->createdAt()->format(\DateTimeInterface::ATOM),
                'updated_at' => $product->updatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $productEntities
        );

        return new self(
            products: $products,
            total: count($products),
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
