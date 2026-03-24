<?php

namespace App\Sales\Application\AddSalesLine;

use App\Sales\Domain\Entity\SalesLine;

class AddSalesLineResponse
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $saleId,
        public readonly string $productId,
        public readonly string $userId,
        public readonly int $quantity,
        public readonly int $price,
        public readonly int $taxPercentage,
        public readonly int $subtotal,
        public readonly int $total,
        public readonly string $createdAt,
    ) {}

    public static function create(SalesLine $salesLine): self
    {
        return new self(
            uuid: $salesLine->uuid()->value(),
            saleId: $salesLine->saleId()->value(),
            productId: $salesLine->productId()->value(),
            userId: $salesLine->userId()->value(),
            quantity: $salesLine->quantity()->value(),
            price: $salesLine->price()->value(),
            taxPercentage: $salesLine->taxPercentage()->value(),
            subtotal: $salesLine->subtotal()->value(),
            total: $salesLine->total()->value(),
            createdAt: $salesLine->createdAt()->value()->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'sale_id' => $this->saleId,
            'product_id' => $this->productId,
            'user_id' => $this->userId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'tax_percentage' => $this->taxPercentage,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'created_at' => $this->createdAt,
        ];
    }
}
