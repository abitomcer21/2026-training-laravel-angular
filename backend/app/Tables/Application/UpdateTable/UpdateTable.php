<?php

namespace App\Tables\Application\UpdateTable;

use App\Tables\Domain\Interfaces\TablesRepositoryInterface;
use App\Tables\Domain\ValueObject\TableName;

class UpdateTable
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    ) {}

    public function __invoke(string $id, string $name): ?UpdateTableResponse
    {
        $table = $this->tablesRepository->findById($id);

        if (!$table) {
            return null;
        }

        $table->updateName(TableName::create($name));
        $this->tablesRepository->save($table);

        return UpdateTableResponse::create($table);
    }
}