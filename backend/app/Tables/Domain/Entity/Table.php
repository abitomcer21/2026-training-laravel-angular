<?php

namespace App\Tables\Domain\Entity;

use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;
use App\Tables\Domain\ValueObject\TableName;

class Table
{
    private function __construct(
        private Uuid $id,
        private TableName $name,
        private int $restaurantId,
        private int $zoneId,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        TableName $name,
        int $zoneId,
        int $restaurantId,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $restaurantId,
            $zoneId,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        int $zoneId,
        string $name,
        int $restaurantId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            TableName::create($name),
            $restaurantId,
            $zoneId,
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
        );
    }

    public function updateData(
        TableName $name,
    ): self {
        return new self(
            $this->id,
            $name,
            $this->restaurantId,
            $this->zoneId,
            $this->createdAt,
            DomainDateTime::now(),
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function zoneId(): int
    {
        return $this->zoneId;
    }

    public function restaurantId(): int
    {
        return $this->restaurantId;
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
}
