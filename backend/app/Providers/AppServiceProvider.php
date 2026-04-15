<?php

namespace App\Providers;

use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\User\Infrastructure\Services\LaravelPasswordHasher;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Infrastructure\Persistence\Repositories\EloquentFamilyRepository;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use App\Restaurants\Domain\Interfaces\RestaurantAdminUserCreatorInterface;
use App\Restaurants\Domain\Interfaces\RestaurantPasswordHasherInterface;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use App\Restaurants\Infrastructure\Persistence\Repositories\EloquentRestaurantRepository;
use App\Restaurants\Infrastructure\Services\CreateRestaurantAdminUser;
use App\Restaurants\Infrastructure\Services\LaravelRestaurantPasswordHasher;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use App\Tax\Infrastructure\Persistence\Repositories\EloquentTaxRepository;
use App\Zones\Domain\Interfaces\ZonesRepositoryInterface;
use App\Zones\Infrastructure\Persistence\Repositories\EloquentZonesRepository;
use App\Tables\Domain\Interfaces\TablesRepositoryInterface;
use App\Tables\Infrastructure\Persistence\Repositories\EloquentTablesRepository;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Infrastructure\Persistence\Repositories\EloquentSalesRepository;
use App\Order\Domain\Interfaces\OrderRepositoryInterface;
use App\Order\Infrastructure\Persistence\Repositories\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

use App\User\Domain\Interfaces\TokenIssuerInterface;
use App\User\Infrastructure\Services\SanctumTokenIssuer;

use App\User\Domain\Interfaces\TokenRevokerInterface;
use App\User\Infrastructure\Services\SanctumTokenRevoker;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(FamilyRepositoryInterface::class, EloquentFamilyRepository::class);
        $this->app->bind(RestaurantRepositoryInterface::class, EloquentRestaurantRepository::class);
        $this->app->bind(RestaurantPasswordHasherInterface::class, LaravelRestaurantPasswordHasher::class);
        $this->app->bind(RestaurantAdminUserCreatorInterface::class, CreateRestaurantAdminUser::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(TaxRepositoryInterface::class, EloquentTaxRepository::class);
        $this->app->bind(ZonesRepositoryInterface::class, EloquentZonesRepository::class);
        $this->app->bind(TablesRepositoryInterface::class, EloquentTablesRepository::class);
        $this->app->bind(SalesRepositoryInterface::class, EloquentSalesRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(TokenIssuerInterface::class, SanctumTokenIssuer::class);
        $this->app->bind(TokenRevokerInterface::class, SanctumTokenRevoker::class);
    }

    public function boot(): void
    {
        //
    }
}
