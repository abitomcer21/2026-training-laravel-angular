<?php

namespace Tests\Unit\Tax\Application\Handler;

use App\Tax\Application\Handler\GetAllTaxHandler;
use App\Tax\Application\Query\GetAllTaxQuery;
use App\Tax\Domain\Entity\Tax;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetAllTaxHandlerTest extends TestCase
{
    private TaxRepositoryInterface&MockObject $taxRepository;
    private GetAllTaxHandler $handler;

    protected function setUp(): void
    {
        $this->taxRepository = $this->createMock(TaxRepositoryInterface::class);
        $this->handler = new GetAllTaxHandler($this->taxRepository);
    }

    public function test_obtiene_todos_los_impuestos_del_restaurante(): void
    {
        $restauranteId = 1;
        $consulta = new GetAllTaxQuery($restauranteId);

        $impuestos = [
            $this->createMock(Tax::class),
            $this->createMock(Tax::class),
        ];

        $this->taxRepository->expects($this->once())
            ->method('findAllByRestaurant')
            ->with($restauranteId)
            ->willReturn($impuestos);

        $respuesta = ($this->handler)($consulta);
        $this->assertCount(2, $respuesta->toArray());
    }

    public function test_devuelve_array_vacio_cuando_no_hay_impuestos(): void
    {
        $restauranteId = 99;
        $consulta = new GetAllTaxQuery($restauranteId);

        $this->taxRepository->expects($this->once())
            ->method('findAllByRestaurant')
            ->with($restauranteId)
            ->willReturn([]);

        $respuesta = ($this->handler)($consulta);
        $this->assertEquals(['tax' => [], 'total' => 0], $respuesta->toArray());
    }
}