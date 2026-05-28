<?php

namespace App\Order\Application\Handler;

use App\Order\Application\Command\CreateOrderCommand;
use App\Order\Application\Response\CreateOrderResponse;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Entity\OrderLine;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Shared\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\TaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;

class CreateOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(CreateOrderCommand $command): CreateOrderResponse
    {
        $orderId = Uuid::generate();

        $orderLines = [];
        foreach ($command->orderLinesData as $lineData) {
            $orderLines[] = OrderLine::dddCreate(
                $command->restaurantId,
                $orderId,
                $lineData['product_id'],
                $lineData['user_id'],
                $lineData['quantity'],
                Price::create($lineData['price']),
                TaxPercentage::create($lineData['tax_percentage']),
            );
        }

        $order = Order::dddCreate(
            $command->restaurantId,
            $command->tableId,
            $command->openedByUserId,
            $command->closedByUserId,
            $command->status,
            $command->diners,
            $orderLines,
        );

        $this->orderRepository->save($order);

        return CreateOrderResponse::create($order);
    }
}
