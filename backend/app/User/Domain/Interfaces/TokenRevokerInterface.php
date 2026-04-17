<?php

namespace App\User\Domain\Interfaces;

interface TokenRevokerInterface
{
    public function revoke(string $token): void;
}
