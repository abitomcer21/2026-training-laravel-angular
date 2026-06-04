<?php

namespace Tests\Unit\Products\Application\Handler;

use App\Products\Application\Handler\GetProductByFamilyHandler;
use App\Products\Application\Query\GetProductByFamilyQuery;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetProductByFamilyHandlerTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepository;
    private GetProductByFamilyHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new GetProductByFamilyHandler($this->productRepository);
    }

    public function test_obtiene_productos_por_familia(): void
    {
        $familiaId = '550e8400-e29b-41d4-a716-446655440000';
        $consulta = new GetProductByFamilyQuery($familiaId, 1);

        $productos = [
            $this->createMock(Product::class),
            $this->createMock(Product::class),
        ];

        $this->productRepository->expects($this->once())
            ->method('findByFamilyId')
            ->with($familiaId)
            ->willReturn($productos);

        $respuesta = ($this->handler)($consulta);
        $array = $respuesta->toArray();

        $this->assertCount(2, $array['products']);
        $this->assertEquals(2, $array['total']);
    }

    public function test_devuelve_array_vacio_si_la_familia_no_tiene_productos(): void
    {
        $familiaId = '550e8400-e29b-41d4-a716-446655440000';
        $consulta = new GetProductByFamilyQuery($familiaId, 1);

        $this->productRepository->expects($this->once())
            ->method('findByFamilyId')
            ->with($familiaId)
            ->willReturn([]);

        $respuesta = ($this->handler)($consulta);
        $array = $respuesta->toArray();

        $this->assertEmpty($array['products']);
        $this->assertEquals(0, $array['total']);
    }
}