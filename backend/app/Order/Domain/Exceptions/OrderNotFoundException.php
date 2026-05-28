<?php

namespace App\Order\Domain\Exceptions;

class OrderNotFoundException extends \DomainException
{
    public function __construct(string $orderId)
    {
        parent::__construct(
            sprintf('Order with ID %s not found', $orderId),
            404,
        );
    }
}
