<?php

namespace App\Products\Application\Handler;

use App\Products\Application\Command\CreateProductCommand;
use App\Products\Application\Response\CreateProductResponse;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\Services\UniqueProductName;
use App\Products\Domain\ValueObject\ProductImageSrc;

class CreateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private UniqueProductName $uniqueProductName,
    ) {}

    public function __invoke(CreateProductCommand $command): CreateProductResponse
    {
        $this->uniqueProductName->check(
            $command->name->value(),
            $command->familyId->value(),
            $command->restaurantId,
        );

        $product = Product::dddCreate(
            familyId:     $command->familyId,
            taxId:        $command->taxId,
            name:         $command->name,
            price:        $command->price,
            stock:        $command->stock,
            imageSrc:     $command->imageSrc ?? ProductImageSrc::create(null),
            status:       $command->status,
            restaurantId: $command->restaurantId,
        );

        $this->productRepository->save($product);

        return CreateProductResponse::create($product);
    }
}