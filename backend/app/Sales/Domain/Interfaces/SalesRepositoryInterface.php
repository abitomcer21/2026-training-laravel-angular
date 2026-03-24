<?php

namespace App\Sales\Domain\Interfaces;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;

interface SalesRepositoryInterface
{
    public function save(Sales $sales): void;

    public function findById(string $id): ?Sales;

    public function saveSalesLine(SalesLine $line): void;

    public function findSalesLinesBySaleId(string $saleId): array;

    public function findSalesLineById(string $id): ?SalesLine;
}

