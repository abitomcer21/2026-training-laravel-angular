<?php

namespace Tests\Unit\Tax\Application\Handler;

use App\Tax\Application\Command\UpdateTaxCommand;
use App\Tax\Application\Handler\UpdateTaxHandler;
use App\Tax\Domain\Entity\Tax;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use App\Tax\Domain\Services\TaxUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateTaxHandlerTest extends TestCase
{
    private TaxRepositoryInterface&MockObject $taxRepository;
    private TaxUpdater&MockObject $taxUpdater;
    private UpdateTaxHandler $handler;

    protected function setUp(): void
    {
        $this->taxRepository = $this->createMock(TaxRepositoryInterface::class);
        $this->taxUpdater    = $this->createMock(TaxUpdater::class);
        $this->handler       = new UpdateTaxHandler($this->taxRepository, $this->taxUpdater);
    }

    public function test_actualiza_impuesto_y_devuelve_respuesta(): void
    {
        $taxId = '550e8400-e29b-41d4-a716-446655440000';
        $nuevoNombre = 'IVA Reducido';
        $nuevoPorcentaje = 10;
        $restauranteId = 1;

        $comando = UpdateTaxCommand::create($taxId, $nuevoNombre, $nuevoPorcentaje, $restauranteId);

        $taxExistente = $this->createMock(Tax::class);
        $taxActualizado = $this->createMock(Tax::class);

        $this->taxRepository->expects($this->once())
            ->method('findById')
            ->with($taxId, $restauranteId)
            ->willReturn($taxExistente);

        $this->taxUpdater->expects($this->once())
            ->method('update')
            ->with($taxExistente, $nuevoNombre, $nuevoPorcentaje)
            ->willReturn($taxActualizado);

        $respuesta = ($this->handler)($comando);
        $this->assertIsArray($respuesta->toArray());
    }

    public function test_lanza_excepcion_si_impuesto_no_existe(): void
    {
        $uuidValido = '550e8400-e29b-41d4-a716-446655440001';
        $comando = UpdateTaxCommand::create($uuidValido, 'Nombre', 10, 1);

        $this->taxRepository->expects($this->once())
            ->method('findById')
            ->with($uuidValido, 1)
            ->willReturn(null);

        $this->taxUpdater->expects($this->never())->method('update');

        $this->expectException(\RuntimeException::class);

        ($this->handler)($comando);
    }
}