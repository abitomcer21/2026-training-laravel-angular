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
        ?string $name,
        ?int $price,
        ?int $stock,
        ?string $imageSrc,
        ?bool $status,
    ): ?UpdateProductResponse {

        $product = $this->productRepository->findById($id);

        if (! $product) {
            return null;
        }

        if ($name === null) {
            $nameVO = $product->name();
        } else {
            $nameVO = ProductName::create($name);
        }

        if ($price === null) {
            $priceVO = $product->price();
        } else {
            $priceVO = ProductPrice::create($price);
        }

        if ($stock === null) {
            $stockVO = $product->stock();
        } else {
            $stockVO = ProductStock::create($stock);
        }

        if ($imageSrc === null) {
            $imageSrcVO = $product->imageSrc();
        } else {
            $imageSrcVO = ProductImageSrc::create($imageSrc);
        }

        if ($status === null) {
            $activeVO = $product->status();
        } else {
            $activeVO = ProductStatus::create($status);
        }

        $product = $product->updateData($nameVO, $priceVO, $stockVO, $imageSrcVO, $activeVO);
        $this->productRepository->save($product);

        return UpdateProductResponse::create($product);
    }
}
