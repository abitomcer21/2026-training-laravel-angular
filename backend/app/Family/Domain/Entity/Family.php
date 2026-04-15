<?php

namespace App\Family\Domain\Entity;

use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\ValueObject\FamilyStatus;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;

class Family
{
    private function __construct(
        private Uuid $id,
        private FamilyName $name,
        private FamilyStatus $status,
        private int $restaurantId,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(FamilyName $name, FamilyStatus $status, int $restaurantId): self
    {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $status,
            $restaurantId,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        bool $active,
        int $restaurantId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            FamilyName::create($name),
            FamilyStatus::create($active),
            $restaurantId,
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
        );
    }

    public function updateData(
        FamilyName $name,
        FamilyStatus $status
    ): self {
        return new self(
            $this->id,
            $name,
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

    public function name(): FamilyName
    {
        return $this->name;
    }

    public function status(): FamilyStatus
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
