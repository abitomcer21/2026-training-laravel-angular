<?php
namespace App\Order\Domain\Entity;

use App\Shared\Domain\ValueObject\Uuid;
use App\Shared\Domain\ValueObject\DomainDateTime;

class OrderLine
{
    private function __construct(
        private Uuid $id,
        private int $restaurantId,
        private Uuid $orderId,
        private string $productId,
        private string $userId,
        private int $quantity,
        private float $price,
        private float $taxPercentage,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        int $restaurantId,
        Uuid $orderId,
        string $productId,
        string $userId,
        int $quantity,
        float $price,
        float $taxPercentage,
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
        string $id,
        int $restaurantId,
        string $orderId,
        string $productId,
        string $userId,
        int $quantity,
        float $price,
        float $taxPercentage,
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
            $price,
            $taxPercentage,
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

    public function price(): float
    {
        return $this->price;
    }

    public function taxPercentage(): float
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