<?php
namespace App\Taxes\Infrastructure\Entrypoint\Http;

use App\Taxes\Application\UpdateTaxes\UpdateTaxes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{
    public function __construct(
        private UpdateTaxes $updateTaxes,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make([
            ...$request->all(),
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $response = ($this->updateTaxes)(
            $id,
            $validated['name'],
            $validated['percentage'],
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Taxes not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
