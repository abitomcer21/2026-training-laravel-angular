<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use App\User\Application\Handler\GetUserByEmailHandler;
use App\User\Application\Query\GetUserByEmailQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUserByEmailController
{
    public function __construct(
        private GetUserByEmailHandler $getUserByEmailHandler,
    ) {}

    public function __invoke(Request $request, string $email): JsonResponse
    {
        try {
            $response = ($this->getUserByEmailHandler)(
                new GetUserByEmailQuery(email: $email),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
