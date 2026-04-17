<?php

namespace App\Sales\Application\AddSalesLine;

use App\Sales\Domain\Entity\SalesLine;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Domain\ValueObject\Quantity;
use App\Sales\Domain\ValueObject\SalesLinePrice;
use App\Sales\Domain\ValueObject\SalesLineTaxPercentage;
use App\Shared\Domain\ValueObject\Uuid;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class AddSalesLine
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(AddSalesLineRequest $request): AddSalesLineResponse
    {
        $saleId = Uuid::create($request->saleId);
        $orderLineId = Uuid::create($request->orderLineId);
        $userId = Uuid::create($request->userId);

        $sale = $this->salesRepository->findById($request->saleId);
        if (! $sale) {
            throw new \InvalidArgumentException('Sale not found.');
        }

        $user = $this->userRepository->findById($request->userId);
        if (! $user) {
            throw new \InvalidArgumentException('User not found.');
        }

        $quantity = Quantity::create($request->quantity);
        $price = SalesLinePrice::create($request->price);
        $taxPercentage = SalesLineTaxPercentage::create($request->taxPercentage);

        $salesLine = SalesLine::dddCreate(
            $saleId,
            $orderLineId,
            $userId,
            $quantity,
            $price,
            $taxPercentage
        );

        $this->salesRepository->saveSalesLine($salesLine);

        return AddSalesLineResponse::create($salesLine);
    }
}
