<?php

namespace App\Taxes\Domain\Entity;

use App\Shared\Domain\ValueObject\Uuid;
use App\Taxes\Domain\ValueObject\TaxName;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Taxes\Domain\ValueObject\TaxPercentage;

class Taxes
{
    private function __construct(
        private Uuid $id,
        private TaxName $name,
        private TaxPercentage $percentage,
        private int $restaurantId,
        private DomainDateTime $createdAt,
        private DomainDateTime $updatedAt,
    ) {}

    public static function dddCreate(TaxName $name, TaxPercentage $percentage, int $restaurantId): self
    {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $name,
            $percentage,
            $restaurantId,
            $now,
            $now,
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        int $percentage,
        int $restaurantId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            Uuid::create($id),
            TaxName::create($name),
            TaxPercentage::create($percentage),
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

    public function percentage(): TaxPercentage
    {
        return $this->percentage;
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

    public function updateDetails(TaxName $name, TaxPercentage $percentage): void
{
    $this->name = $name;
    $this->percentage = $percentage;
    $this->updatedAt = DomainDateTime::now();
}

}
