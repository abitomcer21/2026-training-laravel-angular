<?php
namespace App\Sales\Domain\Entity;

use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Shared\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\TaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;

class SalesLine
{
    private function __construct(
        private Uuid            $id,
        private int             $restaurantId,
        private Uuid            $saleId,
        private Uuid            $orderLineId,
        private string          $userId,
        private int             $quantity,
        private Price           $price,
        private TaxPercentage   $taxPercentage,
        private DomainDateTime  $createdAt,
        private DomainDateTime  $updatedAt,
        private ?DomainDateTime $deletedAt,
    ) {}

    public static function create(
        int           $restaurantId,
        Uuid          $orderLineId,
        string        $userId,
        int           $quantity,
        Price         $price,
        TaxPercentage $taxPercentage,
    ): self {
        $now = DomainDateTime::now();

        return new self(
            Uuid::generate(),
            $restaurantId,
            Uuid::generate(), // saleId se asigna desde fuera, ver nota abajo
            $orderLineId,
            $userId,
            $quantity,
            $price,
            $taxPercentage,
            $now,
            $now,
            null,
        );
    }

    public static function fromPersistence(
        string $id,
        int $restaurantId,
        string $saleId,
        string $orderLineId,
        string $userId,
        int $quantity,
        int $price,
        int $taxPercentage,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        ?\DateTimeImmutable $deletedAt = null,
    ): self {
        return new self(
            Uuid::create($id),
            $restaurantId,
            Uuid::create($saleId),
            Uuid::create($orderLineId),
            $userId,
            $quantity,
            Price::create($price),
            TaxPercentage::create($taxPercentage),
            DomainDateTime::create($createdAt),
            DomainDateTime::create($updatedAt),
            $deletedAt !== null ? DomainDateTime::create($deletedAt) : null,
        );
    }

    public function id(): Uuid                     { return $this->id; }
    public function restaurantId(): int            { return $this->restaurantId; }
    public function saleId(): Uuid                 { return $this->saleId; }
    public function orderLineId(): Uuid            { return $this->orderLineId; }
    public function userId(): string               { return $this->userId; }
    public function quantity(): int                { return $this->quantity; }
    public function price(): Price                 { return $this->price; }
    public function taxPercentage(): TaxPercentage { return $this->taxPercentage; }
    public function createdAt(): DomainDateTime    { return $this->createdAt; }
    public function updatedAt(): DomainDateTime    { return $this->updatedAt; }
    public function deletedAt(): ?DomainDateTime   { return $this->deletedAt; }
}