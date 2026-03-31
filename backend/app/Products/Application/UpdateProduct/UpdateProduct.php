<?php

namespace App\Products\Application\UpdateProduct;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\ProductStock;

class UpdateProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(
        string $id,
        string $name,
        int $price,
        int $stock,
        string $imageSrc,
        bool $active,
    ): ?UpdateProductResponse {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return null;
        }

        $product->updateName(ProductName::create($name));
        $product->updatePrice(ProductPrice::create($price));
    $product->updateImageSrc(ProductImageSrc::create($imageSrc));
        $product->updateStock(ProductStock::create($stock));
        $product->updateStatus(ProductStatus::create($active));

        $this->productRepository->save($product);

        return UpdateProductResponse::create($product);
    }
}