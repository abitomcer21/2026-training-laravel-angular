<?php

namespace App\Tables\Application\GetAllTables;

use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class GetAllTables
{
    public function __construct(private TablesRepositoryInterface $tablesRepository) {}

    public function __invoke(): GetAllTablesResponse
    {
        $tables = $this->tablesRepository->all();

        return GetAllTablesResponse::create($tables);
    }
}
