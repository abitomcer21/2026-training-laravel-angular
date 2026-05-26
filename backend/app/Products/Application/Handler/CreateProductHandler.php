<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Command\CreateProductCommand;
use App\Products\Application\Response\CreateProductResponse;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class CreateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(CreateProductCommand $command): CreateProductResponse
    {
        $product = Product::dddCreate(
            familyId:     $command->familyId,
            taxId:        $command->taxId,
            name:         $command->name,
            price:        $command->price,
            stock:        $command->stock,
            imageSrc:     $command->imageSrc,
            status:       $command->status,
            restaurantId: $command->restaurantId,
        );

        $this->productRepository->save($product);

        return CreateProductResponse::create($product);
    }
}