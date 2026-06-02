<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\Handler\GetFamilyByIdHandler;
use App\Family\Application\Query\GetFamilyByIdQuery;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetFamilyByIdHandler $getFamilyByIdHandler,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $response = ($this->getFamilyByIdHandler)(
                new GetFamilyByIdQuery(
                    id:           $id,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (FamilyNotFoundException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}