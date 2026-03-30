<?php

namespace App\Products\Domain\Entity;

use App\Products\Domain\ValueObject\ImageSrc;
use App\Products\Domain\ValueObject\Price;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\Stock;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;

class Product
{
    private function __construct(
        private Uuid $id,
        private Uuid $familyId,
        private Uuid $taxId,
        private ProductName $name,
        private Price $price,
        private Stock $stock,
        private ImageSrc $imageSrc,
        private ProductStatus $status,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
        private ?DomainDateTime $deletedAt = null,
    ) {}

    public static function dddCreate(
        Uuid $familyId,
        Uuid $taxId,
        ProductName $name,
        Price $price,
        Stock $stock,
        ImageSrc $imageSrc,
        ProductStatus $status,
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
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt = null,
    ): self {
        return new self(
            Uuid::create($id),
            Uuid::create($familyId),
            Uuid::create($taxId),
            ProductName::create($name),
            Price::create($price),
            Stock::create($stock),
            ImageSrc::create($imageSrc),
            ProductStatus::create($active),
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
            $deletedAt ? DomainDateTime::create($deletedAt) : null,
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

    public function price(): Price
    {
        return $this->price;
    }

    public function stock(): Stock
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

    public function createdAt(): DomainDateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DomainDateTime
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?DomainDateTime
    {
        return $this->deletedAt;
    }
}
