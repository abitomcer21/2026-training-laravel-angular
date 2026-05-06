<?php

namespace App\Order\Application\CreateOrder;


use App\Order\Domain\Entity\Order;
use App\Order\Domain\Entity\OrderLine;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Shared\Domain\ValueObject\Uuid;

class CreateOrder
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(
        int $restaurantId,
        string $tableId,
        string $openedByUserId,
        ?string $closedByUserId,
        string $status,
        int $diners,
        array $orderLinesData = [],
    ): CreateOrderResponse {
        $statusVO = OrderStatus::create($status);

        $order = Order::dddCreate(
            $restaurantId,
            $tableId,
            $openedByUserId,
            $closedByUserId,
            $statusVO,
            $diners,
        );
        foreach ($orderLinesData as $lineData) {
            $orderLine = OrderLine::dddCreate(
                $restaurantId,
                Uuid::create($order->id()->value()),
                    (int)$lineData['product_id'],
                (int)$lineData['user_id'],
                $lineData['quantity'],
                $lineData['price'],
                $lineData['tax_percentage'],
            );
            $order->addOrderLine($orderLine);
        }

        $this->orderRepository->save($order);

        return CreateOrderResponse::create($order);
    }
}
