<?php

namespace App\Products\Domain\Interfaces;

use App\Products\Domain\Entity\Products;

interface ProductsRepositoryInterface
{
    public function save(Products $products): void;

    public function findById(string $id): ?Products;
}
