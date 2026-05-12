<?php
namespace App\Sales\Application\CreateSale;

use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Domain\ValueObject\TicketNumber;
use App\Sales\Domain\ValueObject\Total;

class CreateSale
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private SalesRepositoryInterface $salesRepository,
    ) {}

    public function __invoke(string $orderId, string $userId): CreateSalesResponse
    {
        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            throw new \RuntimeException("Order not found: {$orderId}");
        }

        if (empty($order->orderLines())) {
            throw new \RuntimeException("Order has no lines.");
        }

        $totalCents = 0;
        $salesLines = [];

        foreach ($order->orderLines() as $orderLine) {
            $totalCents += $orderLine->price()->cents()
                * $orderLine->quantity()
                * (1 + $orderLine->taxPercentage()->value() / 100);

            $salesLines[] = SalesLine::create(
                $order->restaurantId(),
                $orderLine->id(),
                $orderLine->userId(),
                $orderLine->quantity(),
                $orderLine->price(),
                $orderLine->taxPercentage(),
            );
        }

        $ticketNumber = TicketNumber::create(
            $this->salesRepository->nextTicketNumber()
        );

        $sale = Sales::dddCreate(
            $order->restaurantId(),
            $order->id(),
            $userId,
            $ticketNumber,
            Total::create((int) round($totalCents)),
            $salesLines,
        );

        $this->salesRepository->save($sale);

        return CreateSalesResponse::create($sale);
    }
}