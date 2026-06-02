<?php

namespace Tests\Unit\User\Application\Handler;

use App\User\Application\Command\LoginUserCommand;
use App\User\Application\Handler\LoginUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\PasswordHasherInterface;
use App\User\Domain\Interfaces\TokenIssuerInterface;
use App\User\Domain\Interfaces\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginUserHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private PasswordHasherInterface&MockObject $passwordHasher;
    private TokenIssuerInterface&MockObject $tokenIssuer;
    private LoginUserHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->tokenIssuer    = $this->createMock(TokenIssuerInterface::class);
        $this->handler = new LoginUserHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->tokenIssuer,
        );
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

    public function test_login_returns_token_on_valid_credentials(): void
    {
        $user = $this->makeUser();
        $email = 'test@example.com';
        $password = 'secret123';

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->passwordHasher->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->tokenIssuer->expects($this->once())
            ->method('issueForUser')
            ->with($user)
            ->willReturn('my-token');

        $response = ($this->handler)(LoginUserCommand::create($email, $password));
        $array = $response->toArray();

        $this->assertSame('my-token', $array['token']);
        $this->assertSame($email, $array['user']['email']);
    }

    public function test_login_throws_when_user_not_found(): void
    {
        $email = 'noexiste@example.com';

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->passwordHasher->expects($this->never())->method('verify');
        $this->tokenIssuer->expects($this->never())->method('issueForUser');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Credenciales inválidas');

        ($this->handler)(LoginUserCommand::create($email, 'any-password'));
    }

    public function test_login_throws_on_wrong_password(): void
    {
        $user = $this->makeUser();
        $email = 'test@example.com';
        $wrongPassword = 'wrong';

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->passwordHasher->expects($this->once())
            ->method('verify')
            ->willReturn(false);

        $this->tokenIssuer->expects($this->never())->method('issueForUser');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Credenciales inválidas');

        ($this->handler)(LoginUserCommand::create($email, $wrongPassword));
    }
}