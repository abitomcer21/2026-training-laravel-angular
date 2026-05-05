<?php

namespace App\Family\Application\UpdateFamily;

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
    ): ?UpdateFamilyResponse {

        $family = $this->familyRepository->findById($id);

        if (! $family) {
            return null;
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

        $Family = $family->updateData($nameVO, $isActive);
        $this->familyRepository->save($Family);

        // Sincronizar estado de productos con el estado de la familia
        if ($status !== null) {
            $this->syncProductsStatus($id, $status);
        }

        return UpdateFamilyResponse::create($Family);
    }

    private function syncProductsStatus(string $familyId, bool $status): void
    {
        $products = $this->productRepository->findByFamilyId($familyId);

        foreach ($products as $product) {
            $updatedProduct = $product->updateData(
                $product->FamilyId(),
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
