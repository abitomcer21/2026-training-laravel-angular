<?php

use App\User\Infrastructure\Entrypoint\Http\GetController as UserGetController;
use App\User\Infrastructure\Entrypoint\Http\GetAllController as UserGetAllController;
use App\User\Infrastructure\Entrypoint\Http\PostController as UserPostController;
use App\User\Infrastructure\Entrypoint\Http\PutController as UserPutController;
use App\User\Infrastructure\Entrypoint\Http\DeleteController as UserDeleteController;

use App\Families\Infrastructure\Entrypoint\Http\DeleteController as FamiliesDeleteController;
use App\Families\Infrastructure\Entrypoint\Http\GetController as FamiliesGetController;
use App\Families\Infrastructure\Entrypoint\Http\PostController as FamiliesPostController;
use App\Families\Infrastructure\Entrypoint\Http\PutController as FamiliesPutController;

use App\Products\Infrastructure\Entrypoint\Http\PostController as ProductsPostController;

use App\Taxes\Infrastructure\Entrypoint\Http\PostController as TaxesPostController;
use App\Taxes\Infrastructure\Entrypoint\Http\GetAllController as TaxesGetAllController;
use App\Taxes\Infrastructure\Entrypoint\Http\GetController as TaxesGetController;
use App\Taxes\Infrastructure\Entrypoint\Http\DeleteController as TaxesDeleteController;
use App\Taxes\Infrastructure\Entrypoint\Http\PutController as TaxesPutController;

use App\Zones\Infrastructure\Entrypoint\Http\PostController as ZonesPostController;

use App\Tables\Infrastructure\Entrypoint\Http\PostController as TablesPostController;

use App\Sales\Infrastructure\Entrypoint\Http\PostController as SalesPostController;

use App\Sales\Infrastructure\Entrypoint\Http\AddLineController;
use Illuminate\Support\Facades\Route;

Route::post('/users', UserPostController::class);
Route::get('/users', UserGetAllController::class);
Route::get('/users/{id}', UserGetController::class);
Route::put('/users/{id}', UserPutController::class);
Route::delete('/users/{id}', UserDeleteController::class);

Route::post('/families', FamiliesPostController::class);
Route::get('/families/{id}', FamiliesGetController::class);
Route::put('/families/{id}', FamiliesPutController::class);
Route::delete('/families/{id}', FamiliesDeleteController::class);

Route::post('/products', ProductsPostController::class);

Route::post('/taxes', TaxesPostController::class);
Route::get('/taxes', TaxesGetAllController::class);
Route::get('/taxes/{id}', TaxesGetController::class);
Route::put('/taxes/{id}', TaxesPutController::class);
Route::delete('/taxes/{id}', TaxesDeleteController::class);

Route::post('/zones', ZonesPostController::class);

Route::post('/tables', TablesPostController::class);

Route::post('/sales', SalesPostController::class);

Route::post('/sales/{id}/lines', AddLineController::class);         