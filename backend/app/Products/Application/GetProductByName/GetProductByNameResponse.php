<?php
namespace App\Products\Application\GetProductByName;

use App\Products\Domain\Entity\Product;

final readonly class GetProductByNameResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $price,
        public int $stock,
        public bool $active,
        public int $restaurantId,
        public int $familyId,
        public int $taxId,
        public ?string $imageSrc,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function create(Product $product): self
    {
        return new self(
            id:           $product->id()->value(),
            name:         $product->name(),
            price:        $product->price()->value(),
            stock:        $product->stock()->value(),
            active:       $product->status()->value(),
            restaurantId: $product->restaurantId(),
            familyId:     $product->familyId(),
            taxId:        $product->taxId(),
            imageSrc:     $product->imageSrc(),
            createdAt:    $product->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt:    $product->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'price'         => $this->price,
            'stock'         => $this->stock,
            'active'        => $this->active,
            'restaurant_id' => $this->restaurantId,
            'family_id'     => $this->familyId,
            'tax_id'        => $this->taxId,
            'image_src'     => $this->imageSrc,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}