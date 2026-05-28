<?php

namespace App\Sales\Application\Handler;

use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Sales\Application\Command\CreateSaleCommand;
use App\Sales\Application\Response\CreateSaleResponse;
use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Domain\ValueObject\TicketNumber;
use App\Sales\Domain\ValueObject\Total;

class CreateSaleHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private SalesRepositoryInterface $salesRepository,
    ) {}

    public function __invoke(CreateSaleCommand $command): CreateSaleResponse
    {
        $order = $this->orderRepository->findById($command->orderId);

        if ($order === null) {
            throw new \RuntimeException("Order not found: {$command->orderId}");
        }

        if (empty($order->orderLines())) {
            throw new \RuntimeException('Order has no lines.');
        }

        $totalCents = 0;
        $salesLines = [];

        foreach ($order->orderLines() as $orderLine) {
            $totalCents += $orderLine->price()->cents() * $orderLine->quantity();

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
            $this->salesRepository->nextTicketNumber(),
        );

        $sale = Sales::dddCreate(
            $order->restaurantId(),
            $order->id(),
            $command->userId,
            $ticketNumber,
            Total::create($totalCents),
            $salesLines,
        );

        $this->salesRepository->save($sale);

        return CreateSaleResponse::create($sale);
    }
}
