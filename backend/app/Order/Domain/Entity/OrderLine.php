<?php

namespace App\Order\Domain\Entity;

use App\Shared\Domain\ValueObject\TaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Price;


class OrderLine
{
    private function __construct(
        private Uuid            $id,
        private int             $restaurantId,
        private Uuid            $orderId,
        private string          $productId,
        private string          $userId,
        private int             $quantity,
        private Price           $price,
        private TaxPercentage   $taxPercentage,
        private DomainDateTime  $createdAt,
        private DomainDateTime  $updatedAt,
    ) {}

    public static function dddCreate(
        int           $restaurantId,
        Uuid          $orderId,
        string        $productId,
        string        $userId,
        int           $quantity,
        Price         $price,
        TaxPercentage $taxPercentage,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $restaurantId,
            $orderId,
            $productId,
            $userId,
            $quantity,
            $price,
            $taxPercentage,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string             $id,
        int                $restaurantId,
        string             $orderId,
        string             $productId,
        string             $userId,
        int                $quantity,
        int                $price,
        int                $taxPercentage,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            $restaurantId,
            Uuid::create($orderId),
            $productId,
            $userId,
            $quantity,
            Price::create($price),
            TaxPercentage::create($taxPercentage),
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }
    public function restaurantId(): int
    {
        return $this->restaurantId;
    }
    public function orderId(): Uuid
    {
        return $this->orderId;
    }
    public function productId(): string
    {
        return $this->productId;
    }
    public function userId(): string
    {
        return $this->userId;
    }
    public function quantity(): int
    {
        return $this->quantity;
    }
    public function price(): Price
    {
        return $this->price;
    }
    public function taxPercentage(): TaxPercentage
    {
        return $this->taxPercentage;
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
