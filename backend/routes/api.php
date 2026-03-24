<?php

use App\User\Infrastructure\Entrypoint\Http\PostController as UserPostController;
use App\Families\Infrastructure\Entrypoint\Http\PostController as FamiliesPostController;
use App\Products\Infraestructure\Entrypoint\Http\PostController as ProductsPostController;
use App\Taxes\Infraestructure\Entrypoint\Http\PostController as TaxesPostController;
use App\Zones\Infraestructure\Entrypoint\Http\PostController as ZonesPostController;
use App\Tables\Infraestructure\Entrypoint\Http\PostController as TablesPostController;
use App\Sales\Infraestructure\Entrypoint\Http\PostController as SalesPostController;
use App\Sales\Infraestructure\Entrypoint\Http\AddLineController;
use Illuminate\Support\Facades\Route;

Route::post('/users', UserPostController::class);
Route::post('/families', FamiliesPostController::class);
Route::post('/products', ProductsPostController::class);
Route::post('/taxes', TaxesPostController::class);
Route::post('/zones', ZonesPostController::class);
Route::post('/tables', TablesPostController::class);
Route::post('/sales', SalesPostController::class);
Route::post('/sales/{sale_id}/lines', AddLineController::class);
