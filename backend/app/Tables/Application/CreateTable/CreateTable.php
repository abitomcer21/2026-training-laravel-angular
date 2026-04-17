<?php

namespace App\Tables\Application\CreateTable;

use App\Tables\Domain\Entity\Table;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;
use App\Tables\Domain\ValueObject\TableName;

class CreateTable
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(
        int $zoneId,
        string $name,
        int $restaurantId,
    ): CreateTableResponse {
        $nameVO = TableName::create($name);

        $table = Table::dddCreate(
            $nameVO,
            $zoneId,
            $restaurantId,
        );

        $this->tablesRepository->save($table);

        return CreateTableResponse::create($table);
    }
}
