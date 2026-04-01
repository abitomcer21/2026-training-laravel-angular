<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\UpdateTable\UpdateTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{
    public function __construct(
        private UpdateTable $updateTable,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make([
            ...$request->all(),
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $response = ($this->updateTable)(
            $id,
            $validated['name'],
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Table not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}