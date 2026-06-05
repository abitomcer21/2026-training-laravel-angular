<?php

namespace Tests\Unit\Tax\Domain\ValueObject;

use App\Tax\Domain\ValueObject\TaxPercentage;
use PHPUnit\Framework\TestCase;

class TaxPercentageTest extends TestCase
{
    public function test_crea_porcentaje_valido(): void
    {
        $porcentaje = TaxPercentage::create(21);
        $this->assertSame(21, $porcentaje->value());
    }

    public function test_crea_porcentaje_cero(): void
    {
        $porcentaje = TaxPercentage::create(0);
        $this->assertSame(0, $porcentaje->value());
    }

    public function test_crea_porcentaje_cien(): void
    {
        $porcentaje = TaxPercentage::create(100);
        $this->assertSame(100, $porcentaje->value());
    }

    public function test_lanza_excepcion_si_es_negativo(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tax percentage must be between 0 and 100.');
        TaxPercentage::create(-5);
    }

    public function test_lanza_excepcion_si_supera_cien(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tax percentage must be between 0 and 100.');
        TaxPercentage::create(150);
    }
}