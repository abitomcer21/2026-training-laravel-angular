<?php

namespace App\Products\Application\CreateProduct;

use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\ProductStock;

class CreateProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(
        int $familyId,
        int $taxId,
        int $restaurantId,
        string $name,
        int $price,
        int $stock,
        string $imageSrc,
        bool $active,
    ): CreateProductResponse {
        $nameVO = ProductName::create($name);
        $priceVO = ProductPrice::create($price);
        $stockVO = ProductStock::create($stock);
        $imageSrcVO = ProductImageSrc::create($imageSrc);
        $statusVO = ProductStatus::create($active);

        $product = Product::dddCreate(
            $familyId,
            $taxId,
            $nameVO,
            $priceVO,
            $stockVO,
            $imageSrcVO,
            $statusVO,
            $restaurantId,
        );

        $this->productRepository->save($product);

        return CreateProductResponse::create($product);
    }
}