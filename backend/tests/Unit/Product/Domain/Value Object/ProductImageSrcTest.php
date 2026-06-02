<?php

namespace Tests\Unit\Products\Domain\ValueObject;

use App\Products\Domain\ValueObject\ProductImageSrc;
use PHPUnit\Framework\TestCase;

class ProductImageSrcTest extends TestCase
{
    public function test_crea_con_ruta_nula(): void
    {
        $imagen = ProductImageSrc::create(null);
        $this->assertNull($imagen->path());
    }

    public function test_crea_con_ruta_vacia_se_convierte_a_nulo(): void
    {
        $imagen = ProductImageSrc::create('');
        $this->assertNull($imagen->path());
    }

    public function test_crea_con_ruta_solo_espacios_se_convierte_a_nulo(): void
    {
        $imagen = ProductImageSrc::create('   ');
        $this->assertNull($imagen->path());
    }

    public function test_crea_con_ruta_valida_y_elimina_espacios(): void
    {
        $imagen = ProductImageSrc::create('  /imagenes/producto.jpg  ');
        $this->assertSame('/imagenes/producto.jpg', $imagen->path());
    }

    public function test_lanza_excepcion_cuando_ruta_es_demasiado_larga(): void
    {
        $rutaLarga = str_repeat('a', 256);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La ruta de la imagen no puede exceder 255 caracteres.');
        ProductImageSrc::create($rutaLarga);
    }
}