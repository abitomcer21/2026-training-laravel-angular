<?php

namespace Tests\Unit\Tax\Domain\ValueObject;

use App\Tax\Domain\ValueObject\TaxName;
use PHPUnit\Framework\TestCase;

class TaxNameTest extends TestCase
{
    public function test_crea_nombre_valido(): void
    {
        $nombre = TaxName::create('IVA');
        $this->assertSame('IVA', $nombre->value());
    }

    public function test_elimina_espacios(): void
    {
        $nombre = TaxName::create('  IVA  ');
        $this->assertSame('IVA', $nombre->value());
    }

    public function test_lanza_excepcion_si_esta_vacio(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tax name cannot be empty.');
        TaxName::create('');
    }

    public function test_lanza_excepcion_si_es_demasiado_largo(): void
    {
        $demasiadoLargo = str_repeat('A', 256);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tax name cannot exceed 255 characters.');
        TaxName::create($demasiadoLargo);
    }
}