<?php

namespace App\Tables\Application\DeleteTable;

use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class DeleteTable{
    public function __construct(private TablesRepositoryInterface $tablesRepository)
    {}

    public function __invoke (string $id): bool
    {
        if (!$this->tablesRepository->findById($id)) {
            return false;
        }

        $this->tablesRepository->delete($id);

        return true;
    }
}