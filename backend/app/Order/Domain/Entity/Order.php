<?php

namespace App\Order\Domain\Entity;

use App\Order\Domain\ValueObject\OrderStatus;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;

class Order
{
    private function __construct(
        private Uuid $id,
        private int $restaurantId,
        private string $tableId,
        private string $openedByUserId,
        private string $closedByUserId,
        private OrderStatus $status,
        private int $diners,
        private DomainDateTime $openedAt,
        private ?DomainDateTime $closedAt,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        int $restaurantId,
        string $tableId,
        string $openedByUserId,
        string $closedByUserId,
        OrderStatus $status,
        int $diners,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $restaurantId,
            $tableId,
            $openedByUserId,
            $closedByUserId,
            $status,
            $diners,
            $now,
            null,
            $now,
            $now,
            null,
        );
    }

    public static function fromPersistence(
        string $id,
        int $restaurantId,
        string $tableId,
        string $openedByUserId,
        string $closedByUserId,
        string $status,
        int $diners,
        \DateTimeImmutable $openedAt,
        \DateTimeImmutable $closedAt,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            $restaurantId,
            $tableId,
            $openedByUserId,
            $closedByUserId,
            OrderStatus::create($status),
            $diners,
            DomainDateTime::create($openedAt),
            $closedAt !== null ? DomainDateTime::create($closedAt) : null,
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

    public function tableId(): string
    {
        return $this->tableId;
    }

    public function openedByUserId(): string
    {
        return $this->openedByUserId;
    }

    public function closedByUserId(): ?string
    {
        return $this->closedByUserId;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function diners(): int
    {
        return $this->diners;
    }

    public function openedAt(): DomainDateTime
    {
        return $this->openedAt;
    }

    public function closedAt(): ?DomainDateTime
    {
        return $this->closedAt;
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
