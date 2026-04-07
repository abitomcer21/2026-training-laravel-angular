<?php

namespace App\Restaurants\Infrastructure\Entrypoint\Http;

use App\Restaurants\Application\UpdateRestaurant\UpdateRestaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{
    public function __construct(
        private UpdateRestaurant $updateRestaurant,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make([
            ...$request->all(),
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $response = ($this->updateRestaurant)(
            $id,
            $validated['name'],
            $validated['legal_name'],
            $validated['tax_id'],
            $validated['email'],
            $validated['password'] ?? null,
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Restaurant not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}