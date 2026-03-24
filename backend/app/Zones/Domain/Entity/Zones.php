<?php

namespace App\Zones\Domain\Entity;

use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Uuid;
use App\Zones\Domain\ValueObject\ZoneName;

class Zones
{
    private function __construct(
        private Uuid $id,
        private ZoneName $name,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
        private ?DomainDateTime $deletedAt = null,
    ) {}

    public static function dddCreate(ZoneName $name): self
    {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt = null,
    ): self {
        return new self(
            Uuid::create($id),
            ZoneName::create($name),
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
