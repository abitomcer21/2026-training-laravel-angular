<?php

namespace App\Sales\Domain\Interfaces;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Entity\SalesLine;

interface SalesRepositoryInterface
{
    public function save(Sales $sales): void;

    public function findById(string $id): ?Sales;
    
}
