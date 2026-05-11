<?php

namespace App\Sales\Application\CreateSale;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;
use DateTimeInterface;

final readonly class CreateSalesResponse
{
    public function __construct(
        public string $id,
        public string $orderId,
        public string $userId,
        public int $total,
        public array $salesLines = [],
    ) {}

    public static function create(Sales $sale): self
    {
        $lines = array_map(function (SalesLine $line) {
            return [
                'id' => $line->id()->value(),
                'order_line_id' => $line->orderLineId()->value(),
                'user_id' => $line->userId(),
                'quantity' => $line->quantity(),
                'price' => $line->price()->cents(),
                'tax_percentage' => $line->taxPercentage()->value(),
                'created_at' => $line->createdAt()->format(DateTimeInterface::ATOM),
                'updated_at' => $line->updatedAt()->format(DateTimeInterface::ATOM),
            ];
        }, $sale->salesLines());

        return new self(
            id: $sale->id()->value(),
            orderId: $sale->orderId()->value(),
            userId: $sale->userId(),
            total: $sale->total()->cents(),
            salesLines: $lines,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'total' => $this->total,
            'sales_lines' => $this->salesLines,
        ];
    }
}