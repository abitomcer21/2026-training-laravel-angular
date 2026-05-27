<?php

namespace App\Products\Application\Command;

use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductStock;
use App\Shared\Domain\ValueObject\Uuid;

final readonly class UpdateProductCommand
{
    private function __construct(
        public Uuid $id,
        public ?Uuid $familyId,
        public ?Uuid $taxId,
        public ?ProductName $name,
        public ?ProductPrice $price,
        public ?ProductStock $stock,
        public ?ProductImageSrc $imageSrc,
        public ?bool $active,
    ) {}

    public static function create(
        string $id,
        ?string $familyId,
        ?string $taxId,
        ?string $name,
        ?int $price,
        ?int $stock,
        ?string $imageSrc,
        ?bool $active,
    ): self {
        return new self(
            id:       Uuid::create($id),
            familyId: $familyId !== null ? Uuid::create($familyId) : null,
            taxId:    $taxId !== null ? Uuid::create($taxId) : null,
            name:     $name !== null ? ProductName::create($name) : null,
            price:    $price !== null ? ProductPrice::create($price) : null,
            stock:    $stock !== null ? ProductStock::create($stock) : null,
            imageSrc: $imageSrc !== null ? ProductImageSrc::create($imageSrc) : null,
            active:   $active,
        );
    }
}
