<?php

namespace App\Tables\Application\Handler;

use App\Tables\Application\Query\GetAllTablesQuery;
use App\Tables\Application\Response\GetAllTablesResponse;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class GetAllTablesHandler
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(GetAllTablesQuery $query): GetAllTablesResponse
    {
        $tables = $this->tablesRepository->findAllByRestaurant($query->restaurantId);

        return GetAllTablesResponse::create($tables);
    }
}