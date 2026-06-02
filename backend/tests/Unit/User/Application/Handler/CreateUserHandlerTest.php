<?php

namespace Tests\Unit\User\Application\Handler;

use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Handler\CreateUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateUserHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private PasswordHasherInterface&MockObject $passwordHasher;
    private CreateUserHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->handler = new CreateUserHandler($this->userRepository, $this->passwordHasher);
    }

    public function test_creates_user_and_returns_response(): void
    {
        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with('secret123')
            ->willReturn('$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $command = CreateUserCommand::create(
            email: 'test@example.com',
            name: 'Test User',
            plainPassword: 'secret123',
            role: 'admin',
            pin: '1234',
            restaurantId: 1,
        );

        $response = ($this->handler)($command);
        $array = $response->toArray();

        $this->assertSame('test@example.com', $array['email']);
        $this->assertSame('Test User', $array['name']);
        $this->assertSame('admin', $array['role']);
        $this->assertSame('1234', $array['pin']);
        $this->assertSame(1, $array['restaurant_id']);
    }
}