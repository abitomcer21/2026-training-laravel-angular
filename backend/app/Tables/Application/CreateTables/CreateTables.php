<?php

namespace App\Tables\Application\CreateTables;

use App\Tables\Domain\Entity\Tables;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;
use App\Tables\Domain\ValueObject\TableName;
use App\Shared\Domain\ValueObject\Uuid;

class CreateTables
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(string $zoneId, string $name): CreateTablesResponse
    {
        $nameVO = TableName::create($name);
        $tables = Tables::dddCreate(Uuid::create($zoneId), $nameVO);
        $this->tablesRepository->save($tables);

        return CreateTablesResponse::create($tables);
    }
}
