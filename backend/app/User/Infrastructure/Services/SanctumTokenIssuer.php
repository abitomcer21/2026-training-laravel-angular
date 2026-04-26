<?php

namespace App\User\Infrastructure\Services;

use App\User\Domain\Entity\User;
use App\User\Domain\Interfaces\TokenIssuerInterface;
use App\User\Infrastructure\Persistence\Models\EloquentUser;

class SanctumTokenIssuer implements TokenIssuerInterface
{
    public function issueForUser(User $user): string
    {
        // Buscar por UUID (más rápido que por email y evita query adicional si pasamos el modelo)
        $eloquentUser = EloquentUser::where('uuid', $user->id()->value())->first();

        if ($eloquentUser === null) {
            throw new \RuntimeException('No se pudo emitir el token.');
        }

        return $eloquentUser->createToken('auth-token')->plainTextToken;
    }
}
