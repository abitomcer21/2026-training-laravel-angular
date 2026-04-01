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
        private int $restaurantId,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(
        ZoneName $name,
        int $restaurantId,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $restaurantId,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        int $restaurantId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            ZoneName::create($name),
            $restaurantId,
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
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

    public function restaurantId(): int
    {
        return $this->restaurantId;
    }

    public function createdAt(): DomainDateTime
    {
        return $this->createdAt;
    }

    public function updateName(ZoneName $name): void
    {
        $this->name = $name;
        $this->updatedAt = DomainDateTime::now();
    }

    public function updatedAt(): DomainDateTime
    {
        return $this->updatedAt;
    }

}
