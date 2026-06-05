<?php

namespace Tests\Unit\Tax\Application\Handler;

use App\Tax\Application\Handler\GetTaxByIdHandler;
use App\Tax\Application\Query\GetTaxByIdQuery;
use App\Tax\Domain\Entity\Tax;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetTaxByIdHandlerTest extends TestCase
{
    private TaxRepositoryInterface&MockObject $taxRepository;
    private GetTaxByIdHandler $handler;

    protected function setUp(): void
    {
        $this->taxRepository = $this->createMock(TaxRepositoryInterface::class);
        $this->handler = new GetTaxByIdHandler($this->taxRepository);
    }

    public function test_obtiene_impuesto_por_id(): void
    {
        $taxId = '550e8400-e29b-41d4-a716-446655440000';
        $restauranteId = 1;
        $consulta = new GetTaxByIdQuery($taxId, $restauranteId);

        $taxMock = $this->createMock(Tax::class);

        $this->taxRepository->expects($this->once())
            ->method('findById')
            ->with($taxId, $restauranteId)
            ->willReturn($taxMock);

        $respuesta = ($this->handler)($consulta);
        $this->assertIsArray($respuesta->toArray());
    }

    public function test_lanza_excepcion_si_impuesto_no_existe(): void
    {
        $uuidValido = '550e8400-e29b-41d4-a716-446655440001';
        $restauranteId = 1;
        $consulta = new GetTaxByIdQuery($uuidValido, $restauranteId);

        $this->taxRepository->expects($this->once())
            ->method('findById')
            ->with($uuidValido, $restauranteId)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);

        ($this->handler)($consulta);
    }
}