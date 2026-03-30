<?php
namespace App\Taxes\Infrastructure\Entrypoint\Http;

use App\Taxes\Application\DeleteTaxes\DeleteTaxes;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeleteController 
{
    public function __construct(
        private DeleteTaxes $deleteTaxes,
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

        $deleted = ($this->deleteTaxes)($id);

        if (!$deleted) {
            return new JsonResponse([
                'message' => 'Taxes not found',
            ], 404);
        }

        return new JsonResponse(null, 204);
    }
}