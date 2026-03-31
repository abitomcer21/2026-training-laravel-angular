<?php 

namespace App\Products\Application\DeleteProduct;

use App\Products\Domain\Interfaces\ProductRepositoryInterface;

class DeleteProduct 
{

    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ){}

    public function __invoke (string $id): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return false;
        }

        $product->markAsDeleted();
        $this->productRepository->save($product);

        return true;
    }
}