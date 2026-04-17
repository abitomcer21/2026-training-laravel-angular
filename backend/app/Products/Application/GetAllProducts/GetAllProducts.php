<?php

namespace App\Products\Application\GetAllProducts;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetAllProducts
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(): GetAllProductsResponse
    {
        $product = $this->productRepository->all();

        return GetAllProductsResponse::create($product);
    }
}
