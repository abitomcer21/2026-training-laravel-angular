<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Command\UpdateProductCommand;
use App\Products\Application\Response\UpdateProductResponse;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\Services\ProductUpdater;

class UpdateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductUpdater $productUpdater,
    ) {}

    public function __invoke(UpdateProductCommand $command): UpdateProductResponse
    {
        $product = $this->productRepository->findById(
            $command->id->value(),
            $command->restaurantId
        );

        if ($product === null) {
            throw new ProductNotFoundException($command->id->value());
        }

        $updatedProduct = $this->productUpdater->update(
            product: $product,
            familyId: $command->familyId,
            taxId: $command->taxId,
            name: $command->name,
            price: $command->price,
            stock: $command->stock,
            imageSrc: $command->imageSrc,
            active: $command->active,
        );

        return UpdateProductResponse::create($updatedProduct);
    }
}