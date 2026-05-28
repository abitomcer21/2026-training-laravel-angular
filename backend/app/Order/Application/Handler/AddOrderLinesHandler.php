<?php

namespace App\Order\Application\Handler;

use App\Order\Application\Command\AddOrderLinesCommand;
use App\Order\Application\Response\AddOrderLinesResponse;
use App\Order\Domain\Entity\OrderLine;
use App\Order\Domain\Exceptions\OrderNotFoundException;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Shared\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\TaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;

class AddOrderLinesHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(AddOrderLinesCommand $command): AddOrderLinesResponse
    {
        $order = $this->orderRepository->findById($command->orderId);

        if ($order === null) {
            throw new OrderNotFoundException($command->orderId);
        }

        $orderUuid = Uuid::create($command->orderId);

        foreach ($command->orderLinesData as $lineData) {
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
