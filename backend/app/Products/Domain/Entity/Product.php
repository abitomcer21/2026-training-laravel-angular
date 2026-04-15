<?php

namespace App\Products\Domain\Entity;

use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\ProductStock;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;

class Product
{
    private function __construct(
        private Uuid $id,
        private int $FamilyId,
        private int $taxId,
        private ProductName $name,
        private ProductPrice $price,
        private ProductStock $stock,
        private ProductImageSrc $imageSrc,
        private ProductStatus $status,
        private int $restaurantId,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        int $FamilyId,
        int $taxId,
        ProductName $name,
        ProductPrice $price,
        ProductStock $stock,
        ProductImageSrc $imageSrc,
        ProductStatus $status,
        int $restaurantId,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $FamilyId,
            $taxId,
            $name,
            $price,
            $stock,
            $imageSrc,
            $status,
            $restaurantId,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        int $FamilyId,
        int $taxId,
        string $name,
        int $price,
        int $stock,
        string $imageSrc,
        bool $active,
        int $restaurantId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            $FamilyId,
            $taxId,
            ProductName::create($name),
            ProductPrice::create($price),
            ProductStock::create($stock),
            ProductImageSrc::create($imageSrc),
            ProductStatus::create($active),
            $restaurantId,
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
        );
    }

    public function updateData(
        ProductName $name,
        ProductPrice $price,
        ProductStock $stock,
        ProductImageSrc $imageSrc,
        ProductStatus $status,
    ): self {
        return new self(
            $this->id,
            $this->taxId,
            $this->FamilyId,
            $name,
            $price,
            $stock,
            $imageSrc,
            $status,
            $this->restaurantId,
            $this->createdAt,
            DomainDateTime::now(),
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function FamilyId(): int
    {
        return $this->FamilyId;
    }

    public function taxId(): int
    {
        return $this->taxId;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function price(): ProductPrice
    {
        return $this->price;
    }

    public function stock(): ProductStock
    {
        return $this->stock;
    }

    public function imageSrc(): string
    {
        return $this->imageSrc->value();
    }

    public function status(): ProductStatus
    {
        return $this->status;
    }

    public function restaurantId(): int
    {
        return $this->restaurantId;
    }

    public function createdAt(): DomainDateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DomainDateTime
    {
        return $this->updatedAt;
    }
}
