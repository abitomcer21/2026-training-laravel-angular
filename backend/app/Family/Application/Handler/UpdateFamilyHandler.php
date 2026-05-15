<?php

namespace App\Family\Application\Handler;

use App\Family\Application\Command\UpdateFamilyCommand;
use App\Family\Application\Response\UpdateFamilyResponse;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\ValueObject\FamilyStatus;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\ValueObject\ProductStatus;

class UpdateFamilyHandler

{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(UpdateFamilyCommand $command): UpdateFamilyResponse
    {
        $family = $this->familyRepository->findById($command->id);

        if (! $family) {
            throw new FamilyNotFoundException($command->id);
        }

        $nameVO   = $command->name !== null
            ? FamilyName::create($command->name)
            : $family->name();

        $statusVO = $command->status !== null
            ? FamilyStatus::create($command->status)
            : $family->status();

        $updatedFamily = $family->updateData($nameVO, $statusVO);
        $this->familyRepository->save($updatedFamily);

        if ($command->status !== null) {
            $this->syncProductsStatus($command->id, $command->status);
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