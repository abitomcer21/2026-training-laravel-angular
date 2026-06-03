<?php

namespace Tests\Unit\Products\Application\Handler;

use App\Products\Application\Command\UpdateProductCommand;
use App\Products\Application\Handler\UpdateProductHandler;
use App\Products\Application\Response\UpdateProductResponse;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\Services\ProductUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateProductHandlerTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepository;
    private ProductUpdater&MockObject $productUpdater;
    private UpdateProductHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->productUpdater = $this->createMock(ProductUpdater::class);
        $this->handler = new UpdateProductHandler($this->productRepository, $this->productUpdater);
    }

    public function test_actualiza_producto_y_devuelve_respuesta(): void
    {
        $productoId = '550e8400-e29b-41d4-a716-446655440000';
        $familiaId = '550e8400-e29b-41d4-a716-446655440000';
        $taxId = '550e8400-e29b-41d4-a716-446655440001';
        $nuevoNombre = 'Nuevo Nombre';
        $nuevoPrecio = 1099;
        $stock = 10;
        $imageSrc = null;
        $active = true;
        $restauranteId = 1;

        $comando = UpdateProductCommand::create(
            $productoId,
            $familiaId,
            $taxId,
            $nuevoNombre,
            $nuevoPrecio,
            $stock,
            $imageSrc,
            $active,
            $restauranteId
        );

        $productoExistente = $this->createMock(Product::class);
        $productoActualizado = $this->createMock(Product::class);

        $this->productRepository->expects($this->once())
            ->method('findById')
            ->with($productoId, $restauranteId)
            ->willReturn($productoExistente);

        $this->productUpdater->expects($this->once())
            ->method('update')
            ->willReturn($productoActualizado);

        $respuesta = ($this->handler)($comando);
        $this->assertInstanceOf(UpdateProductResponse::class, $respuesta);
    }

    public function test_lanza_excepcion_si_producto_no_existe(): void
    {
        $uuidValido = '550e8400-e29b-41d4-a716-446655440001';
        $restauranteId = 1;

        $comando = UpdateProductCommand::create(
            $uuidValido,
            null,
            null,
            'Nombre',
            1099,
            5,
            null,
            true,
            $restauranteId
        );

        $this->productRepository->expects($this->once())
            ->method('findById')
            ->with($uuidValido, $restauranteId)
            ->willReturn(null);

        $this->productUpdater->expects($this->never())->method('update');

        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage("Product with ID $uuidValido not found");

        ($this->handler)($comando);
    }
}