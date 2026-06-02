<?php

namespace Tests\Unit\User\Application\Handler;

use App\User\Application\Command\UpdateUserCommand;
use App\User\Application\Handler\UpdateUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateUserHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private PasswordHasherInterface&MockObject $passwordHasher;
    private UpdateUserHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->handler = new UpdateUserHandler($this->userRepository, $this->passwordHasher);
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

    public function test_updates_user_and_returns_response(): void
    {
        $this->userRepository->method('findById')->willReturn($this->makeUser());
        $this->userRepository->expects($this->once())->method('save');

        $command = UpdateUserCommand::create(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: 'nuevo@example.com',
            name: 'Nuevo Nombre',
            plainPassword: null,
            role: 'camarero',
            imageSrc: null,
            pin: null,
        );

        $array = ($this->handler)($command)->toArray();

        $this->assertSame('nuevo@example.com', $array['email']);
        $this->assertSame('Nuevo Nombre', $array['name']);
        $this->assertSame('camarero', $array['role']);
    }

    public function test_updates_password_when_provided(): void
    {
        $this->userRepository->method('findById')->willReturn($this->makeUser());
        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with('nuevapass')
            ->willReturn('$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

        $command = UpdateUserCommand::create(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: null,
            name: null,
            plainPassword: 'nuevapass',
            role: null,
            imageSrc: null,
            pin: null,
        );

        ($this->handler)($command);
    }

    public function test_throws_when_user_not_found(): void
    {
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        ($this->handler)(UpdateUserCommand::create(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: null,
            name: null,
            plainPassword: null,
            role: null,
            imageSrc: null,
            pin: null,
        ));
    }
}