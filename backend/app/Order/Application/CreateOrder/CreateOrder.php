<?php

namespace App\Order\Application\CreateOrder;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Order\Domain\ValueObject\OrderStatus;

class CreateOrder
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(
        int $restaurantId,
        string $tableId,
        string $openedByUserId,
        $closedByUserId,
        string $status,
        int $diners,
    ): CreateOrderResponse {
        $statusVO = OrderStatus::create($status);
        $order = Order::dddCreate($restaurantId, $tableId, $openedByUserId, $closedByUserId, $statusVO, $diners);
        $this->orderRepository->save($order);

        return CreateOrderResponse::create($order);
    }
}
