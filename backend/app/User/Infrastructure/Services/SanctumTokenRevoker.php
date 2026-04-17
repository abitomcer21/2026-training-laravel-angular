<?php

namespace App\User\Infrastructure\Services;

use App\User\Domain\Interfaces\TokenRevokerInterface;
use Laravel\Sanctum\PersonalAccessToken;

class SanctumTokenRevoker implements TokenRevokerInterface
{
    public function revoke(string $token): void
    {
        $accessToken = PersonalAccessToken::findToken($token);

        $accessToken?->delete();
    }
}
