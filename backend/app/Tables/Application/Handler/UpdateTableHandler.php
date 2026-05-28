<?php

namespace App\Tables\Application\Handler;

use App\Tables\Application\Command\UpdateTableCommand;
use App\Tables\Application\Response\UpdateTableResponse;
use App\Tables\Domain\Exceptions\TableNotFoundException;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class UpdateTableHandler
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(UpdateTableCommand $command): UpdateTableResponse
    {
        $table = $this->tablesRepository->findById($command->id->value());

        if ($table === null) {
            throw new TableNotFoundException($command->id->value());
        }

        $name = $command->name ?? $table->name();

        $updatedTable = $table->updateData($name);

        $this->tablesRepository->save($updatedTable);

        return UpdateTableResponse::create($updatedTable);
    }
}