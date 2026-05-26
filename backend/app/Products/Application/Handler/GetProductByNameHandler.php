<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Query\GetProductByNameQuery;
use App\Products\Application\Response\GetProductByNameResponse;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetProductByNameHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(GetProductByNameQuery $query): GetProductByNameResponse
    {
        $product = $this->productRepository->findByName($query->name);

        if ($product === null) {
            throw new ProductNotFoundException($query->name);
        }

        return GetProductByNameResponse::create($product);
    }
}