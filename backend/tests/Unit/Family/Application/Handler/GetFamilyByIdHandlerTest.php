<?php

namespace Tests\Unit\Family\Application\Handler;

use App\Family\Application\Handler\GetFamilyByIdHandler;
use App\Family\Application\Query\GetFamilyByIdQuery;
use App\Family\Application\Response\GetFamilyByIdResponse;
use App\Family\Domain\Entity\Family;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetFamilyByIdHandlerTest extends TestCase
{
    private FamilyRepositoryInterface&MockObject $familyRepository;
    private GetFamilyByIdHandler $handler;

    protected function setUp(): void
    {
        $this->familyRepository = $this->createMock(FamilyRepositoryInterface::class);
        $this->handler = new GetFamilyByIdHandler($this->familyRepository);
    }

    public function test_get_family_by_id_returns_response(): void
    {
        $familyId = '550e8400-e29b-41d4-a716-446655440000';
        $restaurantId = 1;
        $query = new GetFamilyByIdQuery($familyId, $restaurantId);

        $family = Family::fromPersistence(
            id: $familyId,
            name: 'Familia Test',
            active: true,
            restaurantId: $restaurantId,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->familyRepository->expects($this->once())
            ->method('findById')
            ->with($familyId)
            ->willReturn($family);

        $response = ($this->handler)($query);
        $array = $response->toArray();

        $this->assertInstanceOf(GetFamilyByIdResponse::class, $response);
        $this->assertSame('Familia Test', $array['name']);
        $this->assertTrue($array['active']);
        $this->assertSame($restaurantId, $array['restaurantId']);
    }

    public function test_get_family_by_id_throws_when_not_found(): void
    {
        $nonExistingUuid = '550e8400-e29b-41d4-a716-446655440001';
        $restaurantId = 1;
        $query = new GetFamilyByIdQuery($nonExistingUuid, $restaurantId);

        $this->familyRepository->expects($this->once())
            ->method('findById')
            ->with($nonExistingUuid)
            ->willReturn(null);

        $this->expectException(FamilyNotFoundException::class);
        $this->expectExceptionMessage("Family with ID {$nonExistingUuid} not found");

        ($this->handler)($query);
    }
}