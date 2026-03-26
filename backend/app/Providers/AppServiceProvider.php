<?php

namespace App\Providers;

use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\User\Infrastructure\Services\LaravelPasswordHasher;
use App\Families\Domain\Interfaces\FamilyRepositoryInterface;
use App\Families\Infrastructure\Persistence\Repositories\EloquentFamiliesRepository;
use App\Products\Domain\Interfaces\ProductsRepositoryInterface;
use App\Products\Infrastructure\Persistence\Repositories\EloquentProductsRepository;
use App\Restaurants\Domain\Interfaces\RestaurantPasswordHasherInterface;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use App\Restaurants\Infrastructure\Persistence\Repositories\EloquentRestaurantRepository;
use App\Restaurants\Infrastructure\Services\LaravelRestaurantPasswordHasher;
use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;
use App\Taxes\Infrastructure\Persistence\Repositories\EloquentTaxesRepository;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;
use App\Zones\Infrastructure\Persistence\Repositories\EloquentZonesRepository;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;
use App\Tables\Infrastructure\Persistence\Repositories\EloquentTablesRepository;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Infrastructure\Persistence\Repositories\EloquentSalesRepository;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Order\Infrastructure\Persistence\Repositories\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(FamilyRepositoryInterface::class, EloquentFamiliesRepository::class);
        $this->app->bind(RestaurantRepositoryInterface::class, EloquentRestaurantRepository::class);
        $this->app->bind(RestaurantPasswordHasherInterface::class, LaravelRestaurantPasswordHasher::class);
        $this->app->bind(ProductsRepositoryInterface::class, EloquentProductsRepository::class);
        $this->app->bind(TaxesRepositoryInterface::class, EloquentTaxesRepository::class);
        $this->app->bind(ZonesRepositoryInterface::class, EloquentZonesRepository::class);
        $this->app->bind(TablesRepositoryInterface::class, EloquentTablesRepository::class);
        $this->app->bind(SalesRepositoryInterface::class, EloquentSalesRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
