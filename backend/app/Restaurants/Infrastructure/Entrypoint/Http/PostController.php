<?php

namespace App\Restaurants\Infrastructure\Entrypoint\Http;

use App\Restaurants\Application\Command\CreateRestaurantCommand;
use App\Restaurants\Application\Handler\CreateRestaurantHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const REGLAS_VALIDACION = [
        'name'       => ['required', 'string', 'max:255'],
        'legal_name' => ['required', 'string', 'max:255'],
        'tax_id'     => ['required', 'string', 'max:255'],
        'email'      => ['required', 'email', 'max:255'],
        'password'   => ['required', 'string', 'min:8', 'max:255'],
    ];

    public function __construct(
        private CreateRestaurantHandler $createRestaurantHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), self::REGLAS_VALIDACION);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $response = ($this->createRestaurantHandler)(
                CreateRestaurantCommand::create(
                    name:          $validated['name'],
                    legalName:     $validated['legal_name'],
                    taxId:         $validated['tax_id'],
                    email:         $validated['email'],
                    plainPassword: $validated['password'],
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
