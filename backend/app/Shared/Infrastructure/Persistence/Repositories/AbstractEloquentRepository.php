<?php

namespace App\Shared\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;

abstract class AbstractEloquentRepository
{
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollBack(): void
    {
        DB::rollBack();
    }
}