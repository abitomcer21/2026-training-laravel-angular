<?php

namespace App\Sales\Infraestructure\Persistence\Repositories;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Domain\ValueObject\Quantity;
use App\Sales\Domain\ValueObject\SalesLinePrice;
use App\Sales\Domain\ValueObject\SalesLineTaxPercentage;
use App\Sales\Infraestructure\Persistence\Models\EloquentSales;
use App\Sales\Infraestructure\Persistence\Models\EloquentSalesLine;
use App\Shared\Domain\ValueObject\Uuid;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Tables\Infraestructure\Persistence\Models\EloquentTables;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Products\Infraestructure\Persistence\Models\EloquentProducts;

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

    public function saveSalesLine(SalesLine $line): void
    {
        $saleId = EloquentSales::where('uuid', $line->saleId()->value())->first()?->id;
        $productId = EloquentProducts::where('uuid', $line->productId()->value())->first()?->id;
        $userId = EloquentUser::where('uuid', $line->userId()->value())->first()?->id;

        EloquentSalesLine::updateOrCreate(
            ['uuid' => $line->uuid()->value()],
            [
                'sale_id' => $saleId,
                'product_id' => $productId,
                'user_id' => $userId,
                'quantity' => $line->quantity()->value(),
                'price' => $line->price()->value(),
                'tax_percentage' => $line->taxPercentage()->value(),
            ],
        );
    }

    public function findSalesLinesBySaleId(string $saleId): array
    {
        $eloquentSales = EloquentSales::where('uuid', $saleId)->first();

        if (!$eloquentSales) {
            return [];
        }

        $eloquentLines = EloquentSalesLine::where('sale_id', $eloquentSales->id)->get();

        return $eloquentLines->map(function (EloquentSalesLine $eloquentLine) {
            return $this->reconstructSalesLineFromEloquent($eloquentLine);
        })->toArray();
    }

    public function findSalesLineById(string $id): ?SalesLine
    {
        $eloquentLine = EloquentSalesLine::where('uuid', $id)->first();

        if (!$eloquentLine) {
            return null;
        }

        return $this->reconstructSalesLineFromEloquent($eloquentLine);
    }

    private function reconstructSalesLineFromEloquent(EloquentSalesLine $eloquentLine): SalesLine
    {
        $saleUuid = EloquentSales::find($eloquentLine->sale_id)?->uuid;
        $productUuid = EloquentProducts::find($eloquentLine->product_id)?->uuid;
        $userUuid = EloquentUser::find($eloquentLine->user_id)?->uuid;

        return SalesLine::fromPersistence(
            $eloquentLine->uuid,
            $saleUuid,
            $productUuid,
            $userUuid,
            $eloquentLine->quantity,
            $eloquentLine->price,
            $eloquentLine->tax_percentage,
            $eloquentLine->created_at,
            $eloquentLine->updated_at,
            $eloquentLine->deleted_at,
        );
    }
}
