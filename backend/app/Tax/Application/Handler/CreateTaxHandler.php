<?php

namespace App\Tax\Application\Handler;

use App\Tax\Application\Command\CreateTaxCommand;
use App\Tax\Application\Response\CreateTaxResponse;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use App\Tax\Domain\Services\UniqueTaxName;
use App\Tax\Domain\Entity\Tax;

class CreateTaxHandler
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
        private UniqueTaxName $uniqueTaxName,
    ) {}

    public function __invoke(CreateTaxCommand $command): CreateTaxResponse
    {
        $this->uniqueTaxName->check($command->name->value(), $command->restaurantId);

        $tax = Tax::dddCreate(
            $command->name,
            $command->percentage,
            $command->restaurantId,
        );

        $this->taxRepository->save($tax);

        return CreateTaxResponse::create($tax);
    }
}