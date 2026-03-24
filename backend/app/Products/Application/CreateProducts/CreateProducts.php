<?php

namespace App\Products\Application\CreateProducts;

use App\Products\Domain\Entity\Products;
use App\Products\Domain\Interfaces\ProductsRepositoryInterface;
use App\Products\Domain\ValueObject\ImageSrc;
use App\Products\Domain\ValueObject\Price;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\Stock;
use App\Shared\Domain\ValueObject\Uuid;

class CreateProducts
{
    public function __construct(
        private ProductsRepositoryInterface $productsRepository,
    ) {}

    public function __invoke(
        string $familyId,
        string $taxId,
        string $name,
        int $price,
        int $stock,
        string $imageSrc,
        bool $active,
    ): CreateProductsResponse {
        $nameVO = ProductName::create($name);
        $priceVO = Price::create($price);
        $stockVO = Stock::create($stock);
        $imageSrcVO = ImageSrc::create($imageSrc);
        $statusVO = ProductStatus::create($active);
        $products = Products::dddCreate(
            Uuid::create($familyId),
            Uuid::create($taxId),
            $nameVO,
            $priceVO,
            $stockVO,
            $imageSrcVO,
            $statusVO,
        );
        $this->productsRepository->save($products);

        return CreateProductsResponse::create($products);
    }
}
