<?php

namespace App\Sales\Domain\Entity;

use App\Sales\Domain\ValueObject\TicketNumber;
use App\Sales\Domain\ValueObject\Total;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;

class Sales
{
    private array $salesLines;

    private function __construct(
        private Uuid            $id,
        private int             $restaurantId,
        private Uuid            $orderId,
        private string          $userId,
        private ?TicketNumber   $ticketNumber,
        private ?DomainDateTime $valueDate,
        private ?Total          $total,
        private DomainDateTime  $createdAt,
        private DomainDateTime  $updatedAt,
        private ?DomainDateTime $deletedAt,
        array $salesLines = [],
    ) {
        $this->salesLines = $salesLines;
    }

    public static function dddCreate(
        int           $restaurantId,
        Uuid          $orderId,
        string        $userId,
        ?TicketNumber $ticketNumber,
        ?Total        $total,
        array         $salesLines = [],
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $restaurantId,
            $orderId,
            $userId,
            $ticketNumber,
            null,
            $total,
            $now,
            $now,
            null,
            $salesLines,
        );
    }

    public static function fromPersistence(
        string $id,
        int $restaurantId,
        string $orderId,
        string $userId,
        ?string $ticketNumber,
        ?string $valueDate,
        ?int $total,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt,
        array $salesLines = [],
    ): self {
        return new self(
            Uuid::create($id),
            $restaurantId,
            Uuid::create($orderId),
            $userId,
            $ticketNumber !== null ? TicketNumber::create($ticketNumber) : null,
            $valueDate !== null ? DomainDateTime::create(new \DateTimeImmutable($valueDate)) : null,
            $total !== null ? Total::create($total) : null,
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
            $deletedAt !== null ? DomainDateTime::create($deletedAt) : null,
            $salesLines,
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
    public function userId(): string
    {
        return $this->userId;
    }
    public function ticketNumber(): ?TicketNumber
    {
        return $this->ticketNumber;
    }
    public function valueDate(): ?DomainDateTime
    {
        return $this->valueDate;
    }
    public function total(): ?Total
    {
        return $this->total;
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
    public function salesLines(): array
    {
        return $this->salesLines;
    }
}
