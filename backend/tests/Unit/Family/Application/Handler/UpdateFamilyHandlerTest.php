<?php

namespace Tests\Unit\Family\Application\Handler;

use App\Family\Application\Command\UpdateFamilyCommand;
use App\Family\Application\Handler\UpdateFamilyHandler;
use App\Family\Application\Response\UpdateFamilyResponse;
use App\Family\Domain\Entity\Family;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\Services\FamilyUpdater;
use App\Family\Domain\ValueObject\FamilyName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateFamilyHandlerTest extends TestCase
{
    private FamilyRepositoryInterface&MockObject $familyRepository;
    private FamilyUpdater&MockObject $familyUpdater;
    private UpdateFamilyHandler $handler;

    protected function setUp(): void
    {
        $this->familyRepository = $this->createMock(FamilyRepositoryInterface::class);
        $this->familyUpdater = $this->createMock(FamilyUpdater::class);
        $this->handler = new UpdateFamilyHandler($this->familyRepository, $this->familyUpdater);
    }

    private function createRealFamily(string $id, string $name, bool $active, int $restaurantId): Family
    {
        return Family::fromPersistence(
            id: $id,
            name: $name,
            active: $active,
            restaurantId: $restaurantId,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function test_update_family_returns_response(): void
    {
        $familyId = '550e8400-e29b-41d4-a716-446655440000';
        $newName = 'NuevoNombre';
        $newActive = false;
        $restaurantId = 1;

        $command = UpdateFamilyCommand::create($familyId, $newName, $newActive);

        $existingFamily = $this->createRealFamily($familyId, 'Familia Original', true, $restaurantId);
        $updatedFamily = $this->createRealFamily($familyId, $newName, $newActive, $restaurantId);

        $this->familyRepository->expects($this->once())
            ->method('findById')
            ->with($familyId)
            ->willReturn($existingFamily);

        $this->familyUpdater->expects($this->once())
            ->method('update')
            ->with(
                $existingFamily,
                FamilyName::create($newName),
                $newActive
            )
            ->willReturn($updatedFamily);

        $response = ($this->handler)($command);
        $array = $response->toArray();

        $this->assertInstanceOf(UpdateFamilyResponse::class, $response);
        $this->assertSame($familyId, $array['id']);
        $this->assertSame($newName, $array['name']);
        $this->assertSame($newActive, $array['active']);
    }

    public function test_update_family_throws_when_not_found(): void
    {
        $nonExistingUuid = '550e8400-e29b-41d4-a716-446655440001';
        $command = UpdateFamilyCommand::create($nonExistingUuid, 'Nombre', true);

        $this->familyRepository->expects($this->once())
            ->method('findById')
            ->with($nonExistingUuid)
            ->willReturn(null);

        $this->familyUpdater->expects($this->never())->method('update');

        $this->expectException(FamilyNotFoundException::class);
        $this->expectExceptionMessage("Family with ID {$nonExistingUuid} not found");

        ($this->handler)($command);
    }

    public function test_update_family_with_null_name_and_active(): void
    {
        $familyId = '550e8400-e29b-41d4-a716-446655440000';
        $restaurantId = 1;
        $command = UpdateFamilyCommand::create($familyId, null, null);

        $existingFamily = $this->createRealFamily($familyId, 'Original', true, $restaurantId);
        $updatedFamily = $this->createRealFamily($familyId, 'Original', true, $restaurantId);
        $this->familyRepository->expects($this->once())
            ->method('findById')
            ->with($familyId)
            ->willReturn($existingFamily);

        $this->familyUpdater->expects($this->once())
            ->method('update')
            ->with($existingFamily, null, null)
            ->willReturn($updatedFamily);

        $response = ($this->handler)($command);

        $this->assertInstanceOf(UpdateFamilyResponse::class, $response);
    }
}