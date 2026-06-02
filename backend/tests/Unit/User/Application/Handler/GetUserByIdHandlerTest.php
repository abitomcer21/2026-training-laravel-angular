<?php

namespace Tests\Unit\User\Application\Handler;

use App\User\Application\Handler\GetUserByIdHandler;
use App\User\Application\Query\GetUserByIdQuery;
use App\User\Domain\Entity\User;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetUserByIdHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private GetUserByIdHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new GetUserByIdHandler($this->userRepository);
    }

    private function makeUser(): User
    {
        return User::fromPersistence(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Test User',
            email: 'test@example.com',
            passwordHash: '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            role: 'admin',
            restaurantId: 1,
            pin: '1234',
            imageSrc: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function test_returns_user_by_id(): void
    {
        $this->userRepository->method('findById')->willReturn($this->makeUser());

        $array = ($this->handler)(new GetUserByIdQuery('550e8400-e29b-41d4-a716-446655440000'))->toArray();

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $array['id']);
        $this->assertSame('test@example.com', $array['email']);
        $this->assertSame('admin', $array['role']);
    }

    public function test_throws_when_user_not_found(): void
    {
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        ($this->handler)(new GetUserByIdQuery('550e8400-e29b-41d4-a716-446655440000'));
    }
}