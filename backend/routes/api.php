<?php

use App\User\Infrastructure\Entrypoint\Http\PostController as UserPostController;
use App\Families\Infrastructure\Entrypoint\Http\DeleteController as FamiliesDeleteController;
use App\Families\Infrastructure\Entrypoint\Http\GetController as FamiliesGetController;
use App\Families\Infrastructure\Entrypoint\Http\PostController as FamiliesPostController;
use App\Families\Infrastructure\Entrypoint\Http\PutController as FamiliesPutController;
use App\Products\Infrastructure\Entrypoint\Http\PostController as ProductsPostController;
use App\Taxes\Infrastructure\Entrypoint\Http\PostController as TaxesPostController;
use App\Zones\Infrastructure\Entrypoint\Http\PostController as ZonesPostController;
use App\Tables\Infrastructure\Entrypoint\Http\PostController as TablesPostController;
use App\Sales\Infrastructure\Entrypoint\Http\PostController as SalesPostController;
use App\Sales\Infrastructure\Entrypoint\Http\AddLineController;
use Illuminate\Support\Facades\Route;

Route::post('/users', UserPostController::class);
Route::post('/families', FamiliesPostController::class);
Route::get('/families/{id}', FamiliesGetController::class);
Route::put('/families/{id}', FamiliesPutController::class);
Route::delete('/families/{id}', FamiliesDeleteController::class);
Route::post('/products', ProductsPostController::class);
Route::post('/taxes', TaxesPostController::class);
Route::post('/zones', ZonesPostController::class);
Route::post('/tables', TablesPostController::class);
Route::post('/sales', SalesPostController::class);
Route::post('/sales/{sale_id}/lines', AddLineController::class);
