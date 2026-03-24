<?php

namespace App\Sales\Application\CreateSales;

use App\Sales\Domain\Entity\Sales;

final readonly class CreateSalesResponse
{
    public function __construct(
        public string $id,
        public string $tableId,
        public string $openedByUserId,
        public ?string $closedByUserId,
        public string $status,
        public int $diners,
        public string $openedAt,
        public ?string $closedAt,
        public ?int $ticketNumber,
        public ?int $total,
    ) {}

    public static function create(Sales $sales): self
    {
        return new self(
            id: $sales->id()->value(),
            tableId: $sales->tableId()->value(),
            openedByUserId: $sales->openedByUserId()->value(),
            closedByUserId: $sales->closedByUserId()?->value(),
            status: $sales->status()->value(),
            diners: $sales->diners()->value(),
            openedAt: $sales->openedAt()->format(\DateTimeInterface::ATOM),
            closedAt: $sales->closedAt()?->format(\DateTimeInterface::ATOM),
            ticketNumber: $sales->ticketNumber()?->value(),
            total: $sales->total()?->value(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->tableId,
            'opened_by_user_id' => $this->openedByUserId,
            'closed_by_user_id' => $this->closedByUserId,
            'status' => $this->status,
            'diners' => $this->diners,
            'opened_at' => $this->openedAt,
            'closed_at' => $this->closedAt,
            'ticket_number' => $this->ticketNumber,
            'total' => $this->total,
        ];
    }
}
