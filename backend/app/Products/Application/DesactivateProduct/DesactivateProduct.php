<?php

namespace App\Products\Application\DesactivateProduct;

use App\Products\Application\UpdateProduct\UpdateProductResponse;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

final class DesactivateProduct
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

        $product->deactivate();
        $this->productRepository->save($product);

        return UpdateProductResponse::create($product);
    }
}
