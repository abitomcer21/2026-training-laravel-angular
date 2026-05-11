<?php
namespace App\Order\Application\CreateOrder;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Entity\OrderLine;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Shared\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\TaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;

class CreateOrder
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {}

    public function __invoke(
        int     $restaurantId,
        string  $tableId,
        string  $openedByUserId,
        ?string $closedByUserId,
        string  $status,
        int     $diners,
        array   $orderLinesData = [],
    ): CreateOrderResponse {
        $statusVO = OrderStatus::create($status);

        $orderId = Uuid::generate();

        $orderLines = [];
        foreach ($orderLinesData as $lineData) {
            $orderLines[] = OrderLine::dddCreate(
                $restaurantId,
                $orderId,
                $lineData['product_id'],
                $lineData['user_id'],
                $lineData['quantity'],
                Price::create($lineData['price']),
                TaxPercentage::create($lineData['tax_percentage']),
            );
        }

        $order = Order::dddCreate(
            $restaurantId,
            $tableId,
            $openedByUserId,
            $closedByUserId,
            $statusVO,
            $diners,
            $orderLines,
        );

        $this->orderRepository->save($order);

        return CreateOrderResponse::create($order);
    }
}