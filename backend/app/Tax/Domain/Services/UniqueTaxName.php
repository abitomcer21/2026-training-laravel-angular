<?php

namespace App\Tax\Domain\Services;

use App\Tax\Domain\Interfaces\TaxRepositoryInterface;

class UniqueTaxName
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function check(string $name, int $restaurantId): void
    {
        $existing = $this->taxRepository->findByName($name, $restaurantId);

        if ($existing !== null) {
            throw new \InvalidArgumentException('El nombre del impuesto ya existe en este restaurante');
        }
    }
}