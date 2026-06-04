<?php

namespace App\Sales\Application\Response;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;

final readonly class CreateSaleResponse
{
    private function __construct(
        private string $id,
        private string $orderId,
        private string $userId,
        private int $ticketNumber,
        private int $total,
        private array $salesLines,
    ) {}

    public static function create(Sales $sale): self
    {
        $lines = array_map(static fn (SalesLine $line): array => [
            'id'             => $line->id()->value(),
            'order_line_id'  => $line->orderLineId()->value(),
            'user_id'        => $line->userId(),
            'quantity'       => $line->quantity(),
            'price'          => $line->price()->cents(),
            'tax_percentage' => $line->taxPercentage()->value(),
            'created_at'     => $line->createdAt()->format(\DateTimeInterface::ATOM),
            'updated_at'     => $line->updatedAt()->format(\DateTimeInterface::ATOM),
        ], $sale->salesLines());

        return new self(
            id:           $sale->id()->value(),
            orderId:      $sale->orderId()->value(),
            userId:       $sale->userId(),
            ticketNumber: $sale->ticketNumber()?->value() ?? 0,
            total:        $sale->total()->cents(),
            salesLines:   $lines,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'order_id'      => $this->orderId,
            'user_id'       => $this->userId,
            'ticket_number' => $this->ticketNumber,
            'total'         => $this->total,
            'sales_lines'   => $this->salesLines,
        ];
    }
}
