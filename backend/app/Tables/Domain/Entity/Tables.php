<?php

namespace App\Tables\Domain\Entity;

use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;
use App\Tables\Domain\ValueObject\TableName;

class Tables
{
    private function __construct(
        private Uuid $id,
        private Uuid $zoneId,
        private TableName $name,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
        private ?DomainDateTime $deletedAt = null,
    ) {}

    public static function dddCreate(Uuid $zoneId, TableName $name): self
    {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $zoneId,
            $name,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $zoneId,
        string $name,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt = null,
    ): self {
        return new self(
            Uuid::create($id),
            Uuid::create($zoneId),
            TableName::create($name),
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
            $deletedAt ? DomainDateTime::create($deletedAt) : null,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function zoneId(): Uuid
    {
        return $this->zoneId;
    }

    public function name(): string
    {
        return $this->name->value();
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
