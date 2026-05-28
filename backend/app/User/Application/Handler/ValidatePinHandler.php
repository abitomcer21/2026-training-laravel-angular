<?php

namespace App\User\Application\Handler;

use App\User\Application\Command\ValidatePinCommand;
use App\User\Application\Response\ValidatePinResponse;
use App\User\Domain\Exceptions\UserNotFoundException;
use App\User\Domain\Interfaces\UserRepositoryInterface;

class ValidatePinHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(ValidatePinCommand $command): ValidatePinResponse
    {
        $user = $this->userRepository->findById($command->userUuid);

        if ($user === null) {
            throw new UserNotFoundException($command->userUuid);
        }

        if (! $user->pin()->hasPin()) {
            throw new \InvalidArgumentException('Este usuario no tiene PIN configurado');
        }

        if ($user->pin()->value() !== $command->pin) {
            throw new \InvalidArgumentException('PIN inválido');
        }

        return ValidatePinResponse::create($user);
    }
}
