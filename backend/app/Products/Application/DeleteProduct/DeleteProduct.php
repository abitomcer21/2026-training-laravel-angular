<?php

namespace App\Products\Application\DeleteProduct;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class DeleteProduct
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(string $id): bool
    {
        if (! $this->productRepository->findById($id)) {
            return false;
        }

        $this->productRepository->delete($id);

        return true;
    }
}
