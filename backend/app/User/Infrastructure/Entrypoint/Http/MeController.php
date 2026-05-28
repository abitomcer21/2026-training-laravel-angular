<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use App\User\Application\Handler\GetMeHandler;
use App\User\Application\Query\GetMeQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController
{
    public function __construct(
        private GetMeHandler $getMeHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if ($authUser === null) {
            return new JsonResponse([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        try {
            $response = ($this->getMeHandler)(
                new GetMeQuery(uuid: $authUser->uuid),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
