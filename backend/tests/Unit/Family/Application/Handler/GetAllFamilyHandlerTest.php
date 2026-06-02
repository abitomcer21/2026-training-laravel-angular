<?php

namespace Tests\Unit\Family\Application\Handler;

use App\Family\Application\Handler\GetAllFamilyHandler;
use App\Family\Application\Query\GetAllFamilyQuery;
use App\Family\Application\Response\GetAllFamilyResponse;
use App\Family\Domain\Entity\Family;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetAllFamilyHandlerTest extends TestCase
{
    private FamilyRepositoryInterface&MockObject $familyRepository;
    private GetAllFamilyHandler $handler;

    protected function setUp(): void
    {
        $this->familyRepository = $this->createMock(FamilyRepositoryInterface::class);
        $this->handler = new GetAllFamilyHandler($this->familyRepository);
    }

    public function test_get_all_families_returns_response(): void
    {
        $restaurantId = 1;
        $query = new GetAllFamilyQuery($restaurantId);

        $families = [
            $this->createMock(Family::class),
            $this->createMock(Family::class),
        ];

        $this->familyRepository->expects($this->once())
            ->method('findAllByRestaurant')
            ->with($restaurantId)
            ->willReturn($families);

        $response = ($this->handler)($query);

        $this->assertInstanceOf(GetAllFamilyResponse::class, $response);
        $this->assertCount(2, $response->toArray());
    }

    public function test_get_all_families_returns_empty_array_when_no_families(): void
    {
        $restaurantId = 99;
        $query = new GetAllFamilyQuery($restaurantId);

        $this->familyRepository->expects($this->once())
            ->method('findAllByRestaurant')
            ->with($restaurantId)
            ->willReturn([]);

        $response = ($this->handler)($query);

        $this->assertInstanceOf(GetAllFamilyResponse::class, $response);
    }
}
