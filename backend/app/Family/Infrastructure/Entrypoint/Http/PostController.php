<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\CreateFamily\CreateFamily;
use App\Family\Domain\Exceptions\EmptyFamilyNameException;
use App\Family\Domain\Exceptions\FamilyNameTooLongException;
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
            'name' => ['required', 'string'],
            'active' => ['required', 'boolean'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $response = ($this->createFamily)(
                $validated['name'],
                $validated['active'],
                $validated['restaurant_id'],
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (EmptyFamilyNameException | FamilyNameTooLongException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}