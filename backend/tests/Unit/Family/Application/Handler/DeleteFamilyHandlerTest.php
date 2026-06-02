<?php

namespace Tests\Unit\Family\Application\Handler;

use App\Family\Application\Command\DeleteFamilyCommand;
use App\Family\Application\Handler\DeleteFamilyHandler;
use App\Family\Domain\Entity\Family;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteFamilyHandlerTest extends TestCase
{
    private FamilyRepositoryInterface&MockObject $familyRepository;
    private DeleteFamilyHandler $handler;

    protected function setUp(): void
    {
        $this->familyRepository = $this->createMock(FamilyRepositoryInterface::class);
        $this->handler = new DeleteFamilyHandler($this->familyRepository);
    }

    public function test_delete_family_removes_when_exists(): void
    {
        $familyId = '550e8400-e29b-41d4-a716-446655440000';
        $restaurantId = 10;
        $command = DeleteFamilyCommand::create($familyId, $restaurantId);

        $familyMock = $this->createMock(Family::class);

        $this->familyRepository->expects($this->once())
            ->method('findById')
            ->with($familyId)
            ->willReturn($familyMock);

        $this->familyRepository->expects($this->once())
            ->method('delete')
            ->with($familyId);

        ($this->handler)($command);
    }

public function test_delete_family_throws_when_not_found(): void
{
    $nonExistingUuid = '550e8400-e29b-41d4-a716-446655440001';
    $command = DeleteFamilyCommand::create($nonExistingUuid, 1);

    $this->familyRepository->expects($this->once())
        ->method('findById')
        ->with($nonExistingUuid)
        ->willReturn(null);

    $this->familyRepository->expects($this->never())->method('delete');

    $this->expectException(FamilyNotFoundException::class);

    ($this->handler)($command);
}
}
