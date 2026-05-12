<?php

namespace App\Family\Application\UpdateFamily;

use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\ValueObject\FamilyStatus;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\ValueObject\ProductStatus;

class UpdateFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(
        string $id,
        ?string $name,
        ?bool $status
    ): UpdateFamilyResponse {

        $family = $this->familyRepository->findById($id);

        if (! $family) {
            throw new FamilyNotFoundException($id);
        }

        if ($name === null) {
            $nameVO = $family->name();
        } else {
            $nameVO = FamilyName::create($name);
        }

        if ($status === null) {
            $isActive = $family->status();
        } else {
            $isActive = FamilyStatus::create($status);
        }

        $updatedFamily = $family->updateData($nameVO, $isActive);
        $this->familyRepository->save($updatedFamily);

        if ($status !== null) {
            $this->syncProductsStatus($id, $status);
        }

        return UpdateFamilyResponse::create($updatedFamily);
    }

    private function syncProductsStatus(string $familyId, bool $status): void
    {
        $products = $this->productRepository->findByFamilyId($familyId);

        foreach ($products as $product) {
            $updatedProduct = $product->updateData(
                $product->familyId(),
                $product->taxId(),
                $product->name(),
                $product->price(),
                $product->stock(),
                $product->imageSrc(),
                ProductStatus::create($status),
            );
            $this->productRepository->save($updatedProduct);
        }
    }
}