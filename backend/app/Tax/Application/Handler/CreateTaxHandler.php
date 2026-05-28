<?php

namespace App\Tax\Application\Handler;

use App\Tax\Application\Command\CreateTaxCommand;
use App\Tax\Application\Response\CreateTaxResponse;
use App\Tax\Domain\Entity\Tax;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;

class CreateTaxHandler
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(CreateTaxCommand $command): CreateTaxResponse
    {
        $tax = Tax::dddCreate(
            $command->name,
            $command->percentage,
            $command->restaurantId,
        );

        $this->taxRepository->save($tax);

        return CreateTaxResponse::create($tax);
    }
}