<?php

namespace Database\Seeders;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;
use App\Sales\Domain\ValueObject\SalesStatus;
use App\Sales\Domain\ValueObject\Diners;
use App\Sales\Domain\ValueObject\Total;
use App\Sales\Domain\ValueObject\Quantity;
use App\Sales\Domain\ValueObject\SalesLinePrice;
use App\Sales\Domain\ValueObject\SalesLineTaxPercentage;
use App\Sales\Infraestructure\Persistence\Repositories\EloquentSalesRepository;
use App\Shared\Domain\ValueObject\Uuid;
use App\Shared\Domain\ValueObject\DomainDateTime;
use App\Tables\Infraestructure\Persistence\Models\EloquentTables;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Products\Infraestructure\Persistence\Models\EloquentProducts;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $repository = new EloquentSalesRepository();

        $table = EloquentTables::first();
        $user = EloquentUser::first();
        $product = EloquentProducts::first();

        if (!$table || !$user || !$product) {
            return;
        }

        $userId = Uuid::create($user->uuid);
        $tableId = Uuid::create($table->uuid);

        $sale = Sales::dddCreate(
            $tableId,
            $userId,
            Diners::create(2),
        );

        $repository->save($sale);

        $saleId = $sale->id();
        $productId = Uuid::create($product->uuid);
        $quantity = Quantity::create(2);
        $price = SalesLinePrice::create(1000); 
        $taxPercentage = SalesLineTaxPercentage::create(21);

        $salesLine = SalesLine::dddCreate(
            $saleId,
            $productId,
            $userId,
            $quantity,
            $price,
            $taxPercentage,
        );

        $repository->saveSalesLine($salesLine);
    }
}
