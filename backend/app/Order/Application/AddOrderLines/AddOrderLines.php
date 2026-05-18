<?php

namespace App\Order\Application\AddOrderLines;

use App\Order\Domain\Entity\Order;
use App\Order\Application\AddOrderLines\AddOrderLinesResponse;
use App\Order\Domain\Entity\OrderLine;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Shared\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\TaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;
use InvalidArgumentException;

final class AddOrderLines
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(string $orderId, array $orderLinesData): AddOrderLinesResponse
    {
        $order = $this->orderRepository->findById($orderId);

        if ($order === null) {
            throw new InvalidArgumentException("Order with uuid '{$orderId}' not found.");
        }

        $orderUuid = Uuid::create($orderId);

        foreach ($orderLinesData as $lineData) {
            $orderLine = OrderLine::dddCreate(
                $order->restaurantId(),
                $orderUuid,
                $lineData['product_id'],
                strval($lineData['user_id']),
                $lineData['quantity'],
                Price::create($lineData['price']),
                TaxPercentage::create($lineData['tax_percentage']),
            );

            $order->addOrderLine($orderLine);
        }

        $order->touch();
        $this->orderRepository->save($order);

        return AddOrderLinesResponse::create($order);
    }
}
