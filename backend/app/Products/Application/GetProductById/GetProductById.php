<?php

namespace App\Products\Application\GetProductById;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetProductById
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(string $id): ?GetProductByIdResponse
    {
        $product = $this->productRepository->findById($id);

        if (! $product) {
            return null;
        }

        return GetProductByIdResponse::create($product);
    }
}
