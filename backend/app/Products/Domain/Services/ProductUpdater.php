<?php

namespace App\Products\Domain\Services;

use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\ProductStock;
use App\Shared\Domain\ValueObject\Uuid;

class ProductUpdater
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function update(
        Product $product,
        ?Uuid $familyId,
        ?Uuid $taxId,
        ?ProductName $name,
        ?ProductPrice $price,
        ?ProductStock $stock,
        ?ProductImageSrc $imageSrc,
        ?bool $active,
    ): Product {
        $newFamilyId = $familyId ?? $product->familyId();
        $newTaxId    = $taxId    ?? $product->taxId();
        $newName     = $name     ?? $product->name();
        $newPrice    = $price    ?? $product->price();
        $newStock    = $stock    ?? $product->stock();
        $newImageSrc = $imageSrc ?? $product->imageSrc();
        $newStatus   = $active !== null ? ProductStatus::create($active) : $product->status();

        $updatedProduct = $product->updateData(
            $newFamilyId,
            $newTaxId,
            $newName,
            $newPrice,
            $newStock,
            $newImageSrc,
            $newStatus,
        );

        try {
            $this->productRepository->beginTransaction();
            $this->productRepository->save($updatedProduct);
            $this->productRepository->commit();
        } catch (\Throwable $e) {
            $this->productRepository->rollBack();
            throw $e;
        }

        return $updatedProduct;
    }
}
