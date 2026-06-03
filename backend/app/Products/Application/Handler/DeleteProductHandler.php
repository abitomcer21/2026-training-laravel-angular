<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Command\DeleteProductCommand;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class DeleteProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(DeleteProductCommand $command): void
    {
        $product = $this->productRepository->findById(
            $command->id->value(),
            $command->restaurantId 
        );

        if ($product === null) {
            throw new ProductNotFoundException($command->id->value());
        }

        $this->productRepository->delete($command->id->value());
    }
}