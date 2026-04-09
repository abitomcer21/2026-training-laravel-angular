<?php
namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\GetProductByName\GetProductByName;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByNameController
{
    public function __construct(
        private GetProductByName $getProductByName,
    ) {}

    public function __invoke(string $name): JsonResponse
    {
        $validator = Validator::make([
            'name' => $name,
        ], [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $response = ($this->getProductByName)($name);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Product not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}