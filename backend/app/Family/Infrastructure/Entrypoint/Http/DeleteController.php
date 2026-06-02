<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\Command\DeleteFamilyCommand;
use App\Family\Application\Handler\DeleteFamilyHandler;
use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeleteController
{
    public function __construct(
        private DeleteFamilyHandler $deleteFamily,
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

        $family = EloquentFamily::where('uuid', $id)->first();
        if (!$family || $family->restaurant_id !== $request->user()->restaurant_id) {
            return new JsonResponse(['message' => 'Family not found'], 404);
        }

        try {
            ($this->deleteFamily)(
                DeleteFamilyCommand::create(
                    id:           $id,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse(null, 204);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}