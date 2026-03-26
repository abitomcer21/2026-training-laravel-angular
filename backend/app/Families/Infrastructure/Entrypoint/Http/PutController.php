<?php

namespace App\Families\Infrastructure\Entrypoint\Http;

use App\Families\Application\UpdateFamily\UpdateFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{
    public function __construct(
        private UpdateFamily $updateFamily,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make([
            ...$request->all(),
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'activo' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $response = ($this->updateFamily)(
            $id,
            $validated['name'],
            $validated['activo'],
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Family not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}