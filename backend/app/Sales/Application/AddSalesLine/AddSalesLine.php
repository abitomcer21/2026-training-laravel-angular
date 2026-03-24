<?php

namespace App\Sales\Application\AddSalesLine;

use App\Sales\Domain\Entity\SalesLine;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Domain\ValueObject\Quantity;
use App\Sales\Domain\ValueObject\SalesLinePrice;
use App\Sales\Domain\ValueObject\SalesLineTaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;
use App\Products\Domain\Interfaces\ProductsRepositoryInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class AddSalesLine
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository,
        private ProductsRepositoryInterface $productsRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function execute(AddSalesLineRequest $request): AddSalesLineResponse
    {
        $saleId = Uuid::create($request->saleId);
        $productId = Uuid::create($request->productId);
        $userId = Uuid::create($request->userId);

        // Validate sale exists
        $sale = $this->salesRepository->findById($request->saleId);
        if (!$sale) {
            throw new \InvalidArgumentException('Sale not found.');
        }

        // Validate product exists
        $product = $this->productsRepository->findById($request->productId);
        if (!$product) {
            throw new \InvalidArgumentException('Product not found.');
        }

        // Validate user exists
        $user = $this->userRepository->findById($request->userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found.');
        }

        $quantity = Quantity::create($request->quantity);
        $price = SalesLinePrice::create($request->price);
        $taxPercentage = SalesLineTaxPercentage::create($request->taxPercentage);

        $salesLine = SalesLine::dddCreate(
            $saleId,
            $productId,
            $userId,
            $quantity,
            $price,
            $taxPercentage
        );

        $this->salesRepository->saveSalesLine($salesLine);

        return AddSalesLineResponse::create($salesLine);
    }
}
