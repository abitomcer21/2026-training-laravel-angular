<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\GetTableById\GetTableById;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(

        private GetTableById $getTablesById,
    ) {}

    public function __invoke(string $id): JsonResponse {
        $validator = Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
        ]);

        if($validator->fails()) {
            return new JsonResponse ([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $response = ($this->getTablesById)($id);

        if($response === null){
            return new JsonResponse([
                'message' => 'Table not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);

    }
}
