<?php

namespace App\Sales\Infraestructure\Persistence\Repositories;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Infraestructure\Persistence\Models\EloquentSales;
use App\Tables\Infraestructure\Persistence\Models\EloquentTables;
use App\User\Infrastructure\Persistence\Models\EloquentUser;

class EloquentSalesRepository implements SalesRepositoryInterface
{
    public function save(Sales $sales): void
    {
        $tableId = EloquentTables::where('uuid', $sales->tableId()->value())->first()?->id;
        $openedByUserId = EloquentUser::where('uuid', $sales->openedByUserId()->value())->first()?->id;
        $closedByUserId = $sales->closedByUserId() ? EloquentUser::where('uuid', $sales->closedByUserId()->value())->first()?->id : null;

        EloquentSales::updateOrCreate(
            ['uuid' => $sales->id()->value()],
            [
                'table_id' => $tableId,
                'opened_by_user_id' => $openedByUserId,
                'closed_by_user_id' => $closedByUserId,
                'status' => $sales->status()->value(),
                'diners' => $sales->diners()->value(),
                'opened_at' => $sales->openedAt()->value(),
                'closed_at' => $sales->closedAt()?->value(),
                'ticket_number' => $sales->ticketNumber()?->value(),
                'total' => $sales->total()?->value(),
            ],
        );
    }

    public function findById(string $id): ?Sales
    {
        $eloquentSale = EloquentSales::where('uuid', $id)->first();

        if (!$eloquentSale) {
            return null;
        }

        $tableUuid = EloquentTables::find($eloquentSale->table_id)?->uuid;
        $openedByUserUuid = EloquentUser::find($eloquentSale->opened_by_user_id)?->uuid;
        $closedByUserUuid = $eloquentSale->closed_by_user_id ? EloquentUser::find($eloquentSale->closed_by_user_id)?->uuid : null;

        return Sales::fromPersistence(
            $eloquentSale->uuid,
            $tableUuid,
            $openedByUserUuid,
            $closedByUserUuid,
            $eloquentSale->status,
            $eloquentSale->diners,
            $eloquentSale->opened_at,
            $eloquentSale->closed_at,
            $eloquentSale->ticket_number,
            $eloquentSale->total,
            $eloquentSale->created_at,
            $eloquentSale->updated_at,
            $eloquentSale->deleted_at,
        );
    }
}
