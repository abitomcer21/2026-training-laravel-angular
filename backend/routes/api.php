<?php

use App\User\Infrastructure\Entrypoint\Http\GetByIdController as UserGetByIdController;
use App\User\Infrastructure\Entrypoint\Http\GetAllController as UserGetAllController;
use App\User\Infrastructure\Entrypoint\Http\PostController as UserPostController;
use App\User\Infrastructure\Entrypoint\Http\PutController as UserPutController;
use App\User\Infrastructure\Entrypoint\Http\DeleteController as UserDeleteController;

use App\Families\Infrastructure\Entrypoint\Http\DeleteController as FamiliesDeleteController;
use App\Families\Infrastructure\Entrypoint\Http\GetByIdController as FamiliesGetByIdController;
use App\Families\Infrastructure\Entrypoint\Http\PostController as FamiliesPostController;
use App\Families\Infrastructure\Entrypoint\Http\PutController as FamiliesPutController;


use App\Taxes\Infrastructure\Entrypoint\Http\PostController as TaxesPostController;
use App\Taxes\Infrastructure\Entrypoint\Http\GetAllController as TaxesGetAllController;
use App\Taxes\Infrastructure\Entrypoint\Http\GetByIdController as TaxesGetByIdController;
use App\Taxes\Infrastructure\Entrypoint\Http\DeleteController as TaxesDeleteController;
use App\Taxes\Infrastructure\Entrypoint\Http\PutController as TaxesPutController;

use Illuminate\Support\Facades\Route;

Route::post('/users', UserPostController::class);
Route::get('/users', UserGetAllController::class);
Route::get('/users/{id}', UserGetByIdController::class);
Route::put('/users/{id}', UserPutController::class);
Route::delete('/users/{id}', UserDeleteController::class);

Route::post('/families', FamiliesPostController::class);
Route::get('/families/{id}', FamiliesGetByIdController::class);
Route::put('/families/{id}', FamiliesPutController::class);
Route::delete('/families/{id}', FamiliesDeleteController::class);

Route::post('/taxes', TaxesPostController::class);
Route::get('/taxes', TaxesGetAllController::class);
Route::get('/taxes/{id}', TaxesGetByIdController::class);
Route::put('/taxes/{id}', TaxesPutController::class);
Route::delete('/taxes/{id}', TaxesDeleteController::class);