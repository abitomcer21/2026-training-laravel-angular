<?php

namespace Tests\Unit\Products\Domain\ValueObject;

use App\Products\Domain\ValueObject\ProductStock;
use PHPUnit\Framework\TestCase;

class ProductStockTest extends TestCase
{
    public function test_crea_stock_valido_con_cantidad_positiva(): void
    {
        $stock = ProductStock::create(10);
        $this->assertSame(10, $stock->quantity());
        $this->assertTrue($stock->isAvailable());
    }

    public function test_crea_stock_valido_con_cantidad_cero(): void
    {
        $stock = ProductStock::create(0);
        $this->assertSame(0, $stock->quantity());
        $this->assertFalse($stock->isAvailable());
    }

    public function test_lanza_excepcion_cuando_cantidad_es_negativa(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El stock no puede ser negativo.');
        ProductStock::create(-5);
    }
}