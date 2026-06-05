<?php

namespace Tests\Unit\Tax\Application\Handler;

use App\Tax\Application\Command\CreateTaxCommand;
use App\Tax\Application\Handler\CreateTaxHandler;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Tax\Domain\Services\UniqueTaxName;

class CreateTaxHandlerTest extends TestCase
{
    private TaxRepositoryInterface&MockObject $taxRepository;
    private UniqueTaxName&MockObject $uniqueTaxName;
    private CreateTaxHandler $handler;

    protected function setUp(): void
    {
        $this->taxRepository = $this->createMock(TaxRepositoryInterface::class);
        $this->uniqueTaxName = $this->createMock(UniqueTaxName::class);
        $this->handler = new CreateTaxHandler($this->taxRepository, $this->uniqueTaxName);
    }
    public function test_crea_impuesto_y_devuelve_respuesta(): void
    {
        $comando = CreateTaxCommand::create('IVA', 21, 1);

        $this->taxRepository->expects($this->once())
            ->method('save')
            ->with($this->anything());

        $respuesta = ($this->handler)($comando);

        $this->assertIsArray($respuesta->toArray());
    }

    public function test_lanza_excepcion_si_nombre_no_es_unico(): void
    {
        $comando = CreateTaxCommand::create('IVA', 21, 1);

        $this->uniqueTaxName->expects($this->once())
            ->method('check')
            ->willThrowException(new \InvalidArgumentException('El nombre del impuesto ya existe en este restaurante'));

        $this->taxRepository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre del impuesto ya existe en este restaurante');

        ($this->handler)($comando);
    }
}
