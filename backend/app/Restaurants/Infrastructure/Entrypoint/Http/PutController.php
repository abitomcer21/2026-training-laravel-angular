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
        $validated = $request->validate([
            'name'      => ['nullable', 'string', 'max:255'],
            'status'    => ['nullable', 'bool'],
        ]);


        $response = ($this->updateFamily)(
            $id,
            $validate['name'] ?? null,
            $validate ['status'] ?? null,
        );

        if ($response === null){
            return new JsonResponse(['message' => 'Family not found'], 404);
        }

        return new JsonResponse($response->toArray(),200);

    }
}