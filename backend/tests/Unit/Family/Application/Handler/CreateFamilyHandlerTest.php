<?php

namespace Tests\Unit\Family\Application\Handler;

use App\Family\Application\Command\CreateFamilyCommand;
use App\Family\Application\Handler\CreateFamilyHandler;
use App\Family\Application\Response\CreateFamilyResponse;
use App\Family\Domain\Entity\Family;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\Services\UniqueFamilyName;
use App\Family\Domain\ValueObject\FamilyName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateFamilyHandlerTest extends TestCase
{
    private FamilyRepositoryInterface&MockObject $familyRepository;
    private UniqueFamilyName&MockObject $uniqueFamilyName;
    private CreateFamilyHandler $handler;

    protected function setUp(): void
    {
        $this->familyRepository = $this->createMock(FamilyRepositoryInterface::class);
        $this->uniqueFamilyName = $this->createMock(UniqueFamilyName::class);
        $this->handler = new CreateFamilyHandler(
            $this->familyRepository,
            $this->uniqueFamilyName
        );
    }

    public function test_create_family_returns_response(): void
    {
        $name = 'Frutas';
        $active = true;
        $restaurantId = 1;

        $command = CreateFamilyCommand::create($name, $active, $restaurantId);

        $this->uniqueFamilyName->expects($this->once())
            ->method('check')
            ->with(FamilyName::create($name), $restaurantId);

        $this->familyRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Family::class));

        $response = ($this->handler)($command);

        $this->assertInstanceOf(CreateFamilyResponse::class, $response);
    }

    public function test_create_family_throws_when_name_not_unique(): void
    {
        $command = CreateFamilyCommand::create('Pérez', true, 1);

        $this->uniqueFamilyName->expects($this->once())
            ->method('check')
            ->willThrowException(new \InvalidArgumentException('El nombre de familia ya existe'));

        $this->familyRepository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre de familia ya existe');

        ($this->handler)($command);
    }
}