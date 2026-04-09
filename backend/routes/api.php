<?php

use App\User\Infrastructure\Entrypoint\Http\GetByIdController as UserGetByIdController;
use App\User\Infrastructure\Entrypoint\Http\GetAllController as UserGetAllController;
use App\User\Infrastructure\Entrypoint\Http\PostController as UserPostController;
use App\User\Infrastructure\Entrypoint\Http\PutController as UserPutController;
use App\User\Infrastructure\Entrypoint\Http\DeleteController as UserDeleteController;
use App\User\Infrastructure\Entrypoint\Http\GetUserByEmailController as UserGetUserByEmailController;

use App\Families\Infrastructure\Entrypoint\Http\DeleteController as FamiliesDeleteController;
use App\Families\Infrastructure\Entrypoint\Http\GetByIdController as FamiliesGetByIdController;
use App\Families\Infrastructure\Entrypoint\Http\PostController as FamiliesPostController;
use App\Families\Infrastructure\Entrypoint\Http\PutController as FamiliesPutController;
use App\Families\Infrastructure\Entrypoint\Http\GetAllController as FamiliesGetAllController;

use App\Products\Infrastructure\Entrypoint\Http\GetByIdController as ProductsGetByIdController;
use App\Products\Infrastructure\Entrypoint\Http\GetAllController as ProductsGetAllController;
use App\Products\Infrastructure\Entrypoint\Http\PostController as ProductsPostController;
use App\Products\Infrastructure\Entrypoint\Http\PutController as ProductsPutController;
use App\Products\Infrastructure\Entrypoint\Http\DeleteController as ProductsDeleteController;
use App\Products\Infrastructure\Entrypoint\Http\GetByNameController as ProductsGetByNameController;
use App\Products\Infrastructure\Entrypoint\Http\GetByFamilyController;

use App\Taxes\Infrastructure\Entrypoint\Http\PostController as TaxesPostController;
use App\Taxes\Infrastructure\Entrypoint\Http\GetAllController as TaxesGetAllController;
use App\Taxes\Infrastructure\Entrypoint\Http\GetByIdController as TaxesGetByIdController;
use App\Taxes\Infrastructure\Entrypoint\Http\DeleteController as TaxesDeleteController;
use App\Taxes\Infrastructure\Entrypoint\Http\PutController as TaxesPutController;

use App\Zones\Infrastructure\Entrypoint\Http\PostController as ZonesPostController;
use App\Zones\Infrastructure\Entrypoint\Http\GetAllController as ZonesGetAllController;
use App\Zones\Infrastructure\Entrypoint\Http\GetByIdController as ZonesGetByIdController;
use App\Zones\Infrastructure\Entrypoint\Http\PutController as ZonesPutController;
use App\Zones\Infrastructure\Entrypoint\Http\DeleteController as ZonesDeleteController;

use App\Tables\Infrastructure\Entrypoint\Http\PostController as TablesPostController;
use App\Tables\Infrastructure\Entrypoint\Http\GetAllController as TablesGetAllController;
use App\Tables\Infrastructure\Entrypoint\Http\GetByIdController as TablesGetByIdController;
use App\Tables\Infrastructure\Entrypoint\Http\PutController as TablesPutController;
use App\Tables\Infrastructure\Entrypoint\Http\DeleteController as TablesDeleteController;

use App\Restaurants\Infrastructure\Entrypoint\Http\PostController as RestaurantsPostController;
use App\Restaurants\Infrastructure\Entrypoint\Http\PutController as RestaurantsPutController;
use App\Restaurants\Infrastructure\Entrypoint\Http\GetMyRestaurantController;

use App\User\Infrastructure\Entrypoint\Http\LoginController;
use App\User\Infrastructure\Entrypoint\Http\LogoutController;
use App\User\Infrastructure\Entrypoint\Http\MeController;

use Illuminate\Support\Facades\Route;

Route::post('/users', UserPostController::class);
Route::get('/users', UserGetAllController::class);
Route::get('/users/email/{email}', UserGetUserByEmailController::class);
Route::get('/users/{id}', UserGetByIdController::class);
Route::put('/users/{id}', UserPutController::class);
Route::delete('/users/{id}', UserDeleteController::class);

Route::post('/families', FamiliesPostController::class);
Route::get('/families/{id}', FamiliesGetByIdController::class);
Route::put('/families/{id}', FamiliesPutController::class);
Route::delete('/families/{id}', FamiliesDeleteController::class);
Route::get('/families', FamiliesGetAllController::class);
Route::get('/products/family/{familyId}', GetByFamilyController::class);

Route::post('/products', ProductsPostController::class);
Route::get('/products', ProductsGetAllController::class);
Route::get('/products/{id}', ProductsGetByIdController::class);
Route::put('/products/{id}', ProductsPutController::class);
Route::delete('/products/{id}', ProductsDeleteController::class);
Route::get('/products/name/{name}', ProductsGetByNameController::class);

Route::post('/taxes', TaxesPostController::class);
Route::get('/taxes', TaxesGetAllController::class);
Route::get('/taxes/{id}', TaxesGetByIdController::class);
Route::put('/taxes/{id}', TaxesPutController::class);
Route::delete('/taxes/{id}', TaxesDeleteController::class);

Route::post('/zones', ZonesPostController::class);
Route::get('/zones', ZonesGetAllController::class);
Route::get('/zones/{id}', ZonesGetByIdController::class);
Route::put('/zones/{id}', ZonesPutController::class);
Route::delete('/zones/{id}', ZonesDeleteController::class);

Route::post('/tables', TablesPostController::class);
Route::get('/tables', TablesGetAllController::class);
Route::get('/tables/{id}', TablesGetByIdController::class);
Route::put('/tables/{id}', TablesPutController::class);
Route::delete('/tables/{id}', TablesDeleteController::class);

Route::post('/restaurants', RestaurantsPostController::class);
Route::put('/restaurants/{id}', RestaurantsPutController::class);
Route::get('/my-restaurant', GetMyRestaurantController::class);

Route::post('/login', LoginController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::get('/auth/me', MeController::class);
});