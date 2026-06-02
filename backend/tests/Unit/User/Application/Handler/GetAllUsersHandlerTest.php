<?php

namespace Tests\Unit\User\Application\Handler;

use App\User\Application\Handler\GetAllUsersHandler;
use App\User\Application\Query\GetAllUsersQuery;
use App\User\Application\Response\GetAllUsersItem;
use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\UserReadRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetAllUsersHandlerTest extends TestCase
{
    private UserReadRepositoryInterface&MockObject $userReadRepository;
    private GetAllUsersHandler $handler;

    protected function setUp(): void
    {
        $this->userReadRepository = $this->createMock(UserReadRepositoryInterface::class);
        $this->handler = new GetAllUsersHandler($this->userReadRepository);
    }

    private function makeItem(int $numericId = 1): GetAllUsersItem
    {
        return new GetAllUsersItem(
            numericId: $numericId,
            user: User::fromPersistence(
                id: '550e8400-e29b-41d4-a716-446655440000',
                name: 'Test User',
                email: 'test@example.com',
                passwordHash: '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                role: 'camarero',
                restaurantId: 1,
                pin: '1234',
                imageSrc: null,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            ),
        );
    }

    public function test_returns_all_users_without_filter(): void
    {
        $this->userReadRepository
            ->expects($this->once())
            ->method('allWithNumericId')
            ->willReturn([$this->makeItem()]);

        $array = ($this->handler)(new GetAllUsersQuery(null))->toArray();

        $this->assertSame(1, $array['total']);
        $this->assertSame('test@example.com', $array['users'][0]['email']);
    }

    public function test_returns_users_filtered_by_restaurant(): void
    {
        $this->userReadRepository
            ->expects($this->once())
            ->method('allByRestaurantIdWithNumericId')
            ->with(1)
            ->willReturn([$this->makeItem(), $this->makeItem(2)]);

        $array = ($this->handler)(new GetAllUsersQuery(1))->toArray();

        $this->assertSame(2, $array['total']);
    }

    public function test_returns_empty_when_no_users(): void
    {
        $this->userReadRepository->method('allWithNumericId')->willReturn([]);

        $array = ($this->handler)(new GetAllUsersQuery(null))->toArray();

        $this->assertSame(0, $array['total']);
        $this->assertEmpty($array['users']);
    }
}