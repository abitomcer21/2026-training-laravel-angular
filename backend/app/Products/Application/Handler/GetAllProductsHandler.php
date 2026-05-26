<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Query\GetAllProductsQuery;
use App\Products\Application\Response\GetAllProductsResponse;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class GetAllProductsHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(GetAllProductsQuery $query): GetAllProductsResponse
    {
        $products = $this->productRepository->findAllByRestaurant($query->restaurantId);

        return GetAllProductsResponse::create($products);
    }
}