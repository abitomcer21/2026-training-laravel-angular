<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Query\GetProductByIdQuery;
use App\Products\Application\Response\GetProductByIdResponse;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetProductByIdHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(GetProductByIdQuery $query): GetProductByIdResponse
    {
        $product = $this->productRepository->findById($query->id);

        if ($product === null) {
            throw new ProductNotFoundException($query->id);
        }

        return GetProductByIdResponse::create($product);
    }
}