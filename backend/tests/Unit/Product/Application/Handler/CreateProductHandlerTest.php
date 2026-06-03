<?php

namespace Tests\Unit\Products\Application\Handler;

use App\Products\Application\Command\CreateProductCommand;
use App\Products\Application\Handler\CreateProductHandler;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use App\Products\Domain\Services\UniqueProductName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateProductHandlerTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepository;
    private UniqueProductName&MockObject $uniqueProductName;
    private CreateProductHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->uniqueProductName = $this->createMock(UniqueProductName::class);
        $this->handler = new CreateProductHandler($this->productRepository, $this->uniqueProductName);
    }

    public function test_crea_producto_y_devuelve_respuesta(): void
    {
        $familiaId = '550e8400-e29b-41d4-a716-446655440000';
        $restauranteId = 1;

        $this->uniqueProductName->expects($this->once())
            ->method('check')
            ->with('Producto Test', $familiaId, $restauranteId);

        $this->productRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Product::class));

        $comando = CreateProductCommand::create(
            familyId: $familiaId,
            taxId: '550e8400-e29b-41d4-a716-446655440001',
            restaurantId: $restauranteId,
            name: 'Producto Test',
            price: 1099,
            stock: 100,
            imageSrc: null,
            active: true,
        );

        $respuesta = ($this->handler)($comando);

        $this->assertIsArray($respuesta->toArray());
    }

    public function test_lanza_excepcion_si_el_nombre_no_es_unico(): void
    {
        $familiaId = '550e8400-e29b-41d4-a716-446655440000';

        $this->uniqueProductName->expects($this->once())
            ->method('check')
            ->willThrowException(new \InvalidArgumentException('El nombre del producto ya existe en esta familia'));

        $this->productRepository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre del producto ya existe en esta familia');

        ($this->handler)(CreateProductCommand::create(
            familyId: $familiaId,
            taxId: '550e8400-e29b-41d4-a716-446655440001',
            restaurantId: 1,
            name: 'Producto Duplicado',
            price: 599,
            stock: 50,
            imageSrc: null,
            active: true,
        ));
    }
}