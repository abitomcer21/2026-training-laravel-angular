<?php

namespace App\Products\Application\Command;

use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductStock;
use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Shared\Domain\ValueObject\Uuid;

final readonly class CreateProductCommand
{
    private function __construct(
        public Uuid $familyId,
        public Uuid $taxId,
        public int $restaurantId,
        public ProductName $name,
        public ProductPrice $price,
        public ProductStock $stock,
        public ?ProductImageSrc $imageSrc,
        public ProductStatus $status,
    ) {}

    public static function create(
        string $familyId,
        string $taxId,
        int $restaurantId,
        string $name,
        int $price,
        int $stock,
        ?string $imageSrc,
        bool $active,
    ): self {
        return new self(
            familyId:     Uuid::create($familyId),
            taxId:        Uuid::create($taxId),
            restaurantId: $restaurantId,
            name:         ProductName::create($name),
            price:        ProductPrice::create($price),
            stock:        ProductStock::create($stock),
            imageSrc:     $imageSrc !== null ? ProductImageSrc::create($imageSrc) : null,
            status:       ProductStatus::create($active),
        );
    }
}