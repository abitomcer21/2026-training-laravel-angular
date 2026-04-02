<?php

namespace App\User\Domain\Interfaces;

use App\User\Domain\Entity\User;

interface TokenIssuerInterface
{
    public function issueForUser(User $user): string;
}
