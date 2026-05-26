<?php

namespace App\Products\Domain\Exceptions;

class ProductNotFoundException extends \DomainException
{
    private string $productId;

    public function __construct(string $productId)
    {
        parent::__construct(
            sprintf('Product with ID %s not found', $productId),
            404
        );
        
        $this->productId = $productId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }
}
