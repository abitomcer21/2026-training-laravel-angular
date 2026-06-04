<?php

namespace Tests\Unit\Products\Application\Handler;

use App\Products\Application\Handler\GetAllProductsHandler;
use App\Products\Application\Query\GetAllProductsQuery;
use App\Products\Domain\Entity\Product;
use App\Products\Domain\Interfaces\ProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetAllProductsHandlerTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $productRepository;
    private GetAllProductsHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new GetAllProductsHandler($this->productRepository);
    }

    public function test_obtiene_todos_los_productos_del_restaurante(): void
    {
        $restauranteId = 1;
        $consulta = new GetAllProductsQuery($restauranteId);

        $productos = [
            $this->createMock(Product::class),
            $this->createMock(Product::class),
        ];

        $this->productRepository->expects($this->once())
            ->method('findAllByRestaurant')
            ->with($restauranteId)
            ->willReturn($productos);

        $respuesta = ($this->handler)($consulta);
        $array = $respuesta->toArray();

        $this->assertCount(2, $array['products']);
        $this->assertEquals(2, $array['total']);
    }

    public function test_devuelve_array_vacio_cuando_no_hay_productos(): void
    {
        $restauranteId = 99;
        $consulta = new GetAllProductsQuery($restauranteId);

        $this->productRepository->expects($this->once())
            ->method('findAllByRestaurant')
            ->with($restauranteId)
            ->willReturn([]);

        $respuesta = ($this->handler)($consulta);
        $array = $respuesta->toArray();

        $this->assertEmpty($array['products']);
        $this->assertEquals(0, $array['total']);
    }
}