<?php
namespace App\Sales\Domain\Interfaces;

use App\Sales\Domain\Entity\Sales;

interface SalesRepositoryInterface
{
    public function save(Sales $sales): void;
    public function findById(string $id): ?Sales;
    public function nextTicketNumber(): int;
    public function getTodaySales(string $date): array;
    public function cancelSale(string $id): void;
    public function cancelSalesLine(string $id): void;
}