<?php

namespace App\Tables\Application\Handler;

use App\Tables\Application\Command\DeleteTableCommand;
use App\Tables\Domain\Exceptions\TableNotFoundException;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class DeleteTableHandler
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(DeleteTableCommand $command): void
    {
        $table = $this->tablesRepository->findById($command->id->value());

        if ($table === null) {
            throw new TableNotFoundException($command->id->value());
        }

        $this->tablesRepository->delete($command->id->value());
    }
}