<?php

namespace App\Sales\Domain\Entity;

use App\Sales\Domain\ValueObject\Diners;
use App\Sales\Domain\ValueObject\SalesStatus;
use App\Sales\Domain\ValueObject\TicketNumber;
use App\Sales\Domain\ValueObject\Total;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;

class Sales
{
    private function __construct(
        private Uuid $id,
        private Uuid $tableId,
        private Uuid $openedByUserId,
        private ?Uuid $closedByUserId,
        private SalesStatus $status,
        private Diners $diners,
        private DomainDateTime $openedAt,
        private ?DomainDateTime $closedAt,
        private ?TicketNumber $ticketNumber,
        private ?Total $total,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
        private ?DomainDateTime $deletedAt = null,
    ) {}

    public static function dddCreate(
        Uuid $tableId,
        Uuid $openedByUserId,
        Diners $diners,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $tableId,
            $openedByUserId,
            null,
            SalesStatus::open(),
            $diners,
            $now,
            null,
            null,
            null,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $tableId,
        string $openedByUserId,
        ?string $closedByUserId,
        string $status,
        int $diners,
        \DateTimeImmutable $openedAt,
        ?\DateTimeImmutable $closedAt,
        ?int $ticketNumber,
        ?int $total,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt = null,
    ): self {
        return new self(
            Uuid::create($id),
            Uuid::create($tableId),
            Uuid::create($openedByUserId),
            $closedByUserId ? Uuid::create($closedByUserId) : null,
            SalesStatus::create($status),
            Diners::create($diners),
            DomainDateTime::create($openedAt),
            $closedAt ? DomainDateTime::create($closedAt) : null,
            $ticketNumber ? TicketNumber::create($ticketNumber) : null,
            $total !== null ? Total::create($total) : null,
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
            $deletedAt ? DomainDateTime::create($deletedAt) : null,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function tableId(): Uuid
    {
        return $this->tableId;
    }

    public function openedByUserId(): Uuid
    {
        return $this->openedByUserId;
    }

    public function closedByUserId(): ?Uuid
    {
        return $this->closedByUserId;
    }

    public function status(): SalesStatus
    {
        return $this->status;
    }

    public function diners(): Diners
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

    public function ticketNumber(): ?TicketNumber
    {
        return $this->ticketNumber;
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
}
