<?php

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Domain\Interfaces\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManagerInterface
{
    public function run(callable $callback): mixed
    {
        DB::beginTransaction();

        try {
            $result = $callback();
            DB::commit();

            return $result;

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}