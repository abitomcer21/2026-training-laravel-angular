<?php
namespace App\Tax\Infrastructure\Entrypoint\Http;

use App\Tax\Application\DeleteTax\DeleteTax;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeleteController 
{
    public function __construct(
        private DeleteTax $deleteTax,
    ){

    }

    public function __invoke(string $id): JsonResponse
    {
        $validator = Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $deleted = ($this->deleteTax)($id);

        if (!$deleted) {
            return new JsonResponse([
                'message' => 'Tax not found',
            ], 404);
        }

        return new JsonResponse(null, 204);
    }
}