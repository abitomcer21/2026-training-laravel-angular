<?php

namespace Tests\Unit\Tax\Application\Handler;

use App\Tax\Application\Command\DeleteTaxCommand;
use App\Tax\Application\Handler\DeleteTaxHandler;
use App\Tax\Domain\Entity\Tax;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteTaxHandlerTest extends TestCase
{
    private TaxRepositoryInterface&MockObject $taxRepository;
    private DeleteTaxHandler $handler;

    protected function setUp(): void
    {
        $this->taxRepository = $this->createMock(TaxRepositoryInterface::class);
        $this->handler = new DeleteTaxHandler($this->taxRepository);
    }

    public function test_elimina_impuesto_si_existe(): void
    {
        $taxId = '550e8400-e29b-41d4-a716-446655440000';
        $restauranteId = 1;
        $comando = DeleteTaxCommand::create($taxId, $restauranteId);

        $taxMock = $this->createMock(Tax::class);

        $this->taxRepository->expects($this->once())
            ->method('findById')
            ->with($taxId, $restauranteId)
            ->willReturn($taxMock);

        $this->taxRepository->expects($this->once())
            ->method('delete')
            ->with($taxId);

        ($this->handler)($comando);
    }

    public function test_lanza_excepcion_si_impuesto_no_existe(): void
    {
        $uuidValido = '550e8400-e29b-41d4-a716-446655440001';
        $restauranteId = 1;
        $comando = DeleteTaxCommand::create($uuidValido, $restauranteId);

        $this->taxRepository->expects($this->once())
            ->method('findById')
            ->with($uuidValido, $restauranteId)
            ->willReturn(null);

        $this->taxRepository->expects($this->never())->method('delete');

        $this->expectException(\RuntimeException::class);

        ($this->handler)($comando);
    }
}