<?php

namespace Tests\Unit\Restaurants\Application\Handler;

use App\Restaurants\Application\Command\CreateRestaurantCommand;
use App\Restaurants\Application\Handler\CreateRestaurantHandler;
use App\Restaurants\Domain\Entity\Restaurant;
use App\Restaurants\Domain\Interfaces\RestaurantAdminUserCreatorInterface;
use App\Restaurants\Domain\Interfaces\RestaurantPasswordHasherInterface;
use App\Restaurants\Domain\Interfaces\RestaurantRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateRestaurantHandlerTest extends TestCase
{
    private RestaurantRepositoryInterface&MockObject $restaurantRepository;
    private RestaurantPasswordHasherInterface&MockObject $passwordHasher;
    private RestaurantAdminUserCreatorInterface&MockObject $adminUserCreator;
    private CreateRestaurantHandler $handler;

    protected function setUp(): void
    {
        $this->restaurantRepository = $this->createMock(RestaurantRepositoryInterface::class);
        $this->passwordHasher       = $this->createMock(RestaurantPasswordHasherInterface::class);
        $this->adminUserCreator     = $this->createMock(RestaurantAdminUserCreatorInterface::class);
        $this->handler = new CreateRestaurantHandler(
            $this->restaurantRepository,
            $this->passwordHasher,
            $this->adminUserCreator,
        );
    }

    public function test_creates_restaurant_and_admin_user(): void
    {
        $this->passwordHasher->method('hash')->willReturn(
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        );

        $this->restaurantRepository->expects($this->once())->method('save')
            ->with($this->isInstanceOf(Restaurant::class));

        $this->restaurantRepository->method('getInternalIdByUuid')->willReturn(42);

        $this->adminUserCreator->expects($this->once())->method('create')
            ->with('rest@example.com', 'Mi Restaurante', 'secret123', 42);

        $command = CreateRestaurantCommand::create(
            name: 'Mi Restaurante',
            legalName: 'Mi Restaurante S.L.',
            taxId: 'B12345678',
            email: 'rest@example.com',
            plainPassword: 'secret123',
        );

        $array = ($this->handler)($command)->toArray();

        $this->assertSame('Mi Restaurante', $array['name']);
        $this->assertSame('Mi Restaurante S.L.', $array['legal_name']);
        $this->assertSame('rest@example.com', $array['email']);
    }

    public function test_throws_when_restaurant_not_persisted(): void
    {
        $this->passwordHasher->method('hash')->willReturn(
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        );
        $this->restaurantRepository->method('getInternalIdByUuid')->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Restaurant could not be persisted correctly.');

        ($this->handler)(CreateRestaurantCommand::create(
            name: 'Mi Restaurante',
            legalName: 'Mi Restaurante S.L.',
            taxId: 'B12345678',
            email: 'rest@example.com',
            plainPassword: 'secret123',
        ));
    }
}