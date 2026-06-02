<?php

namespace Tests\Unit\User\Application\Handler;

use App\User\Application\Command\DeleteUserCommand;
use App\User\Application\Handler\DeleteUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteUserHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private DeleteUserHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new DeleteUserHandler($this->userRepository);
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

    public function test_deletes_user(): void
    {
        $this->userRepository->method('findById')->willReturn($this->makeUser());
        $this->userRepository
            ->expects($this->once())
            ->method('delete')
            ->with('550e8400-e29b-41d4-a716-446655440000');

        ($this->handler)(DeleteUserCommand::create('550e8400-e29b-41d4-a716-446655440000'));
    }

    public function test_throws_when_user_not_found(): void
    {
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        ($this->handler)(DeleteUserCommand::create('550e8400-e29b-41d4-a716-446655440000'));
    }
}