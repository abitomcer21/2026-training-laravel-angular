<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\Command\DeleteTableCommand;
use App\Tables\Application\Handler\DeleteTableHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class DeleteController
{
    public function __construct(
        private DeleteTableHandler $deleteTableHandler,
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
            ($this->deleteTableHandler)(
                DeleteTableCommand::create(
                    id:           $id,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse(null, 204);

} catch (\Throwable $e) {
    return new JsonResponse(['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
}
    }
}