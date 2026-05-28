<?php

namespace App\Tables\Application\Handler;

use App\Tables\Application\Query\GetTableByIdQuery;
use App\Tables\Application\Response\GetTableByIdResponse;
use App\Tables\Domain\Exceptions\TableNotFoundException;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class GetTableByIdHandler
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(GetTableByIdQuery $query): GetTableByIdResponse
    {
        $table = $this->tablesRepository->findById($query->id);

        if ($table === null) {
            throw new TableNotFoundException($query->id);
        }

        return GetTableByIdResponse::create($table);
    }
}