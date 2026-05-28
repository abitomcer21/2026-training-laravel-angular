<?php

namespace App\Tables\Application\Handler;

use App\Tables\Application\Command\CreateTableCommand;
use App\Tables\Application\Response\CreateTableResponse;
use App\Tables\Domain\Entity\Table;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class CreateTableHandler
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(CreateTableCommand $command): CreateTableResponse
    {
        $table = Table::dddCreate(
            $command->name,
            $command->zoneId,
            $command->restaurantId,
        );

        $this->tablesRepository->save($table);

        return CreateTableResponse::create($table);
    }
}