<?php

namespace Tests\Unit\Products\Application\Handler;

use App\Products\Application\Handler\GetProductByNameHandler;
use App\Products\Application\Query\GetProductByNameQuery;
use App\Products\Application\Response\GetProductByNameResponse;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetProductByNameHandlerTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepository;
    private GetProductByNameHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new GetProductByNameHandler($this->productRepository);
    }

    public function test_busca_producto_por_nombre(): void
    {
        $nombre = 'Producto Test';
        $restauranteId = 1;
        $consulta = new GetProductByNameQuery($nombre, $restauranteId);

        $productoMock = $this->createMock(Product::class);

        $this->productRepository->expects($this->once())
            ->method('findByName')
            ->with($nombre, $restauranteId)
            ->willReturn($productoMock);

        $respuesta = ($this->handler)($consulta);
        $this->assertInstanceOf(GetProductByNameResponse::class, $respuesta);
    }

    public function test_lanza_excepcion_si_no_se_encuentra_el_producto(): void
    {
        $consulta = new GetProductByNameQuery('nombre-inexistente', 1);

        $this->productRepository->expects($this->once())
            ->method('findByName')
            ->with('nombre-inexistente', 1)
            ->willReturn(null);

        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage('nombre-inexistente');

        ($this->handler)($consulta);
    }
}