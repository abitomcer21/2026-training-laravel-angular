<?php

namespace App\Sales\Domain\Entity;

use App\Shared\Domain\ValueObject\Uuid;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Sales\Domain\ValueObject\Quantity;
use App\Sales\Domain\ValueObject\SalesLinePrice;
use App\Sales\Domain\ValueObject\SalesLineTaxPercentage;

class SalesLine
{
    private Uuid $uuid;
    private Uuid $saleId;
    private Uuid $orderLineId;
    private Uuid $userId;
    private Quantity $quantity;
    private SalesLinePrice $price;
    private SalesLineTaxPercentage $taxPercentage;
    private DomainDateTime $createdAt;
    private ?DomainDateTime $deletedAt;

    private function __construct(
        Uuid $uuid,
        Uuid $saleId,
        Uuid $orderLineId,
        Uuid $userId,
        Quantity $quantity,
        SalesLinePrice $price,
        SalesLineTaxPercentage $taxPercentage,
        DomainDateTime $createdAt,
        ?DomainDateTime $deletedAt = null
    ) {
        $this->uuid = $uuid;
        $this->saleId = $saleId;
        $this->orderLineId = $orderLineId;
        $this->userId = $userId;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->taxPercentage = $taxPercentage;
        $this->createdAt = $createdAt;
        $this->deletedAt = $deletedAt;
    }

    public static function dddCreate(
        Uuid $saleId,
        Uuid $orderLineId,
        Uuid $userId,
        Quantity $quantity,
        SalesLinePrice $price,
        SalesLineTaxPercentage $taxPercentage
    ): self {
        return new self(
            Uuid::generate(),
            $saleId,
            $orderLineId,
            $userId,
            $quantity,
            $price,
            $taxPercentage,
            DomainDateTime::now(),
            null
        );
    }

    public static function fromPersistence(
        string $uuid,
        string $saleId,
        string $orderLineId,
        string $userId,
        int $quantity,
        int $price,
        int $taxPercentage,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        ?\DateTime $deletedAt = null
    ): self {
        return new self(
            Uuid::create($uuid),
            Uuid::create($saleId),
            Uuid::create($orderLineId),
            Uuid::create($userId),
            Quantity::create($quantity),
            SalesLinePrice::create($price),
            SalesLineTaxPercentage::create($taxPercentage),
            DomainDateTime::create(\DateTimeImmutable::createFromMutable($createdAt)),
            $deletedAt ? DomainDateTime::create(\DateTimeImmutable::createFromMutable($deletedAt)) : null
        );
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function saleId(): Uuid
    {
        return $this->saleId;
    }

    public function orderLineId(): Uuid
    {
        return $this->orderLineId;
    }

    public function userId(): Uuid
    {
        return $this->userId;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function price(): SalesLinePrice
    {
        return $this->price;
    }

    public function taxPercentage(): SalesLineTaxPercentage
    {
        return $this->taxPercentage;
    }

    public function createdAt(): DomainDateTime
    {
        return $this->createdAt;
    }

    public function deletedAt(): ?DomainDateTime
    {
        return $this->deletedAt;
    }

    public function subtotal(): SalesLinePrice
    {
        $subtotal = $this->price->cents() * $this->quantity->quantity();

        return SalesLinePrice::create($subtotal);
    }

    public function total(): SalesLinePrice
    {
        $subtotalCents = $this->subtotal()->cents();
        $taxCents = (int) ($subtotalCents * $this->taxPercentage->asDecimal());
        $totalCents = $subtotalCents + $taxCents;

        return SalesLinePrice::create($totalCents);
    }
}
