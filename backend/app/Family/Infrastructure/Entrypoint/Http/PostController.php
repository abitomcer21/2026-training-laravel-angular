<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\CreateFamily\CreateFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    public function __construct(
        private CreateFamily $createFamily,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
            'restaurant_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $response = ($this->createFamily)(
            $validated['name'],
            $validated['active'],
            $validated['restaurant_id'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
