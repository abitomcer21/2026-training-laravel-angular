<?php

namespace Tests\Unit\Products\Application\Handler;

use App\Products\Application\Command\DeleteProductCommand;
use App\Products\Application\Handler\DeleteProductHandler;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Exceptions\ProductNotFoundException;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteProductHandlerTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepository;
    private DeleteProductHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new DeleteProductHandler($this->productRepository);
    }

    public function test_elimina_producto_si_existe(): void
    {
        $productoId = '550e8400-e29b-41d4-a716-446655440000';
        $restauranteId = 1;
        $comando = DeleteProductCommand::create($productoId, $restauranteId);

        $productoMock = $this->createMock(Product::class);

        $this->productRepository->expects($this->once())
            ->method('findById')
            ->with($productoId)
            ->willReturn($productoMock);

        $this->productRepository->expects($this->once())
            ->method('delete')
            ->with($productoId);

        ($this->handler)($comando);
    }

    public function test_lanza_excepcion_si_producto_no_existe(): void
    {
        $uuidValido = '550e8400-e29b-41d4-a716-446655440001';
        $comando = DeleteProductCommand::create($uuidValido, 1);

        $this->productRepository->expects($this->once())
            ->method('findById')
            ->with($uuidValido)
            ->willReturn(null);

        $this->productRepository->expects($this->never())->method('delete');

        $this->expectException(ProductNotFoundException::class);
        $this->expectExceptionMessage("Product with ID $uuidValido not found");

        ($this->handler)($comando);
    }
}