<?php

namespace App\Products\Application\UpdateProduct;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\ValueObject\ProductImageSrc;
use App\Products\Domain\ValueObject\ProductName;
use App\Products\Domain\ValueObject\ProductPrice;
use App\Products\Domain\ValueObject\ProductStatus;
use App\Products\Domain\ValueObject\ProductStock;
use App\Shared\Domain\ValueObject\Uuid;

class UpdateProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(
        string $id,
        ?string $familyId,
        ?string $taxId,
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

        if ($familyId === null) {
            $familyIdVO = $product->FamilyId();
        } else {
            $familyIdVO = Uuid::create($familyId);
        }

        if ($taxId === null) {
            $taxIdVO = $product->taxId();
        } else {
            $taxIdVO = Uuid::create($taxId);
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

        $product = $product->updateData($familyIdVO, $taxIdVO, $nameVO, $priceVO, $stockVO, $imageSrcVO, $activeVO);
        $this->productRepository->save($product);

        return UpdateProductResponse::create($product);
    }
}
