<?php

namespace App\Shared\Domain\Exceptions;

class InvalidEmailException extends DomainException
{
    protected string $errorCode = 'INVALID_EMAIL';
    protected int $httpStatus = 422;

    public function __construct(string $invalidEmail)
    {
        parent::__construct(sprintf('Invalid email address: %s', $invalidEmail));
        $this->details = ['email' => ['The email field must be a valid email address.']];
    }
}