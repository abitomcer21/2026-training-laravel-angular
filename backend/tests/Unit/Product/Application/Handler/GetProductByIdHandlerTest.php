<?php

namespace Tests\Unit\Products\Application\Handler;

use App\Products\Application\Handler\GetProductByIdHandler;
use App\Products\Application\Query\GetProductByIdQuery;
use App\Products\Application\Response\GetProductByIdResponse;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetProductByIdHandlerTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepository;
    private GetProductByIdHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new GetProductByIdHandler($this->productRepository);
    }

    public function test_obtiene_producto_por_id(): void
    {
        $productoId = '550e8400-e29b-41d4-a716-446655440000';
        $restauranteId = 1;
        $consulta = new GetProductByIdQuery($productoId, $restauranteId);

        $productoMock = $this->createMock(Product::class);

        $this->productRepository->expects($this->once())
            ->method('findById')
            ->with($productoId, $restauranteId)
            ->willReturn($productoMock);

        $respuesta = ($this->handler)($consulta);
        $this->assertInstanceOf(GetProductByIdResponse::class, $respuesta);
    }

    public function test_lanza_excepcion_si_producto_no_existe(): void
    {
        $uuidValido = '550e8400-e29b-41d4-a716-446655440001';
        $restauranteId = 1;
        $consulta = new GetProductByIdQuery($uuidValido, $restauranteId);

        $this->productRepository->expects($this->once())
            ->method('findById')
            ->with($uuidValido, $restauranteId)
            ->willReturn(null);

        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage("Product with ID $uuidValido not found");

        ($this->handler)($consulta);
    }
}