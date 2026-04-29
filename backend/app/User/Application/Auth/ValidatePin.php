<?php

namespace App\User\Application\Auth;

use App\User\Domain\Interfaces\UserRepositoryInterface;

class ValidatePin
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(string $userUuid, string $pin): ValidatePinResponse
    {
        $user = $this->userRepository->findById($userUuid);

        if ($user === null) {
            throw new \InvalidArgumentException('Usuario no encontrado');
        }

        error_log('PIN BD: ' . $user->pin()->value());
        error_log('PIN enviado: ' . $pin);
        error_log('UUID buscado: ' . $userUuid);

        if (! $user->pin()->hasPin()) {
            throw new \InvalidArgumentException('Este usuario no tiene PIN configurado');
        }

        $isValidPin = $user->pin()->value() === $pin;

        if (! $isValidPin) {
            throw new \InvalidArgumentException('PIN inválido');
        }

        return ValidatePinResponse::create($user);
    }
}
