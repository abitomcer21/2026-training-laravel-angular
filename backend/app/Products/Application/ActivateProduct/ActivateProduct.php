<?php

namespace App\Products\Application\ActivateProduct;

use App\Products\Application\UpdateProduct\UpdateProductResponse;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

final class ActivateProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(string $id): ?UpdateProductResponse
    {
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            return null;
        }

        $product->activate();
        $this->productRepository->save($product);

        return UpdateProductResponse::create($product);
    }
}
