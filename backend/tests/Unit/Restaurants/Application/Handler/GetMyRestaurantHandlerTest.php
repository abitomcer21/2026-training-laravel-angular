<?php

namespace Tests\Unit\Restaurants\Application\Handler;

use App\Restaurants\Application\Handler\GetMyRestaurantHandler;
use App\Restaurants\Application\Query\GetMyRestaurantQuery;
use App\Restaurants\Domain\Entity\Restaurant;
use App\Restaurants\Domain\Exceptions\RestaurantNotFoundException;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetMyRestaurantHandlerTest extends TestCase
{
    private RestaurantRepositoryInterface&MockObject $restaurantRepository;
    private GetMyRestaurantHandler $handler;

    protected function setUp(): void
    {
        $this->restaurantRepository = $this->createMock(RestaurantRepositoryInterface::class);
        $this->handler = new GetMyRestaurantHandler($this->restaurantRepository);
    }

    private function makeRestaurant(): Restaurant
    {
        return Restaurant::fromPersistence(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Mi Restaurante',
            legalName: 'Mi Restaurante S.L.',
            taxId: 'B12345678',
            email: 'rest@example.com',
            password: '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            imageSrc: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function test_returns_restaurant(): void
    {
        $this->restaurantRepository->method('findByInternalId')->willReturn($this->makeRestaurant());

        $array = ($this->handler)(new GetMyRestaurantQuery(1))->toArray();

        $this->assertSame('Mi Restaurante', $array['name']);
        $this->assertSame('rest@example.com', $array['email']);
    }

    public function test_throws_when_not_found(): void
    {
        $this->restaurantRepository->method('findByInternalId')->willReturn(null);

        $this->expectException(RestaurantNotFoundException::class);

        ($this->handler)(new GetMyRestaurantQuery(99));
    }
}