<?php

namespace App\Families\Domain\Entity;

use App\Families\Domain\ValueObject\FamilyName;
use App\Families\Domain\ValueObject\FamilyStatus;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;

class Families
{
    private function __construct(
        private Uuid $id,
        private FamilyName $name,
        private FamilyStatus $status,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
        private ?DomainDateTime $deletedAt = null,
    ) {}

    public static function dddCreate(FamilyName $name, FamilyStatus $status): self
    {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $status,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        bool $activo,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt = null,
    ): self {
        return new self(
            Uuid::create($id),
            FamilyName::create($name),
            FamilyStatus::create($activo),
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
            $deletedAt ? DomainDateTime::create($deletedAt) : null,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name->value();
    }

    public function status(): FamilyStatus
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
