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
        private Uuid $familyId,
        private Uuid $taxId,
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
        Uuid $familyId,
        Uuid $taxId,
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
            $familyId,
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
        string $familyId,
        string $taxId,
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
            Uuid::create($familyId),
            Uuid::create($taxId),
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

    public function id(): Uuid
    {
        return $this->id;
    }

    public function familyId(): Uuid
    {
        return $this->familyId;
    }

    public function taxId(): Uuid
    {
        return $this->taxId;
    }

    public function name(): string
    {
        return $this->name->value();
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

    public function updateName(ProductName $name): void
    {
        $this->name = $name;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updatePrice(ProductPrice $price): void
    {
        $this->price = $price;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updateImageSrc(ProductImageSrc $imageSrc): void
    {
        $this->imageSrc = $imageSrc;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updateImagenSRC(ProductImageSrc $imageSrc): void
    {
        $this->updateImageSrc($imageSrc);
    }

    public function updateStock(ProductStock $stock): void
    {
        $this->stock = $stock;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updateStatus(ProductStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = DomainDateTime::now();
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
