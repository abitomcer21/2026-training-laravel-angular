<?php
namespace App\Tables\Application\GetTableById;

use App\Tables\Domain\Interfaces\TablesRepositoryInterface;

class GetTableById
{
    public function __construct(
        private TablesRepositoryInterface $tablesRepository,
    )
    {}

    public function __invoke(string $id): ?GetTableByIdResponse
    {
        $table = $this->tablesRepository->findById($id);

        if(!$table){
            return null;
        }

        return GetTableByIdResponse::create($table);
    }
}