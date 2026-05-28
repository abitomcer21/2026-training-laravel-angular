<?php

namespace App\Order\Infrastructure\Entrypoint\Http;

use App\Order\Application\Command\CreateOrderCommand;
use App\Order\Application\Handler\CreateOrderHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const REGLAS_VALIDACION = [
        'restaurant_id'                    => ['required', 'integer', 'exists:restaurants,id'],
        'table_id'                         => ['required', 'string'],
        'opened_by_user_id'                => ['required', 'integer'],
        'closed_by_user_id'                => ['nullable', 'integer'],
        'status'                           => ['required', 'string', 'in:open,closed,cancelled'],
        'diners'                           => ['required', 'integer', 'min:1'],
        'order_lines'                      => ['required', 'array', 'min:1'],
        'order_lines.*.product_id'         => ['required', 'string', 'uuid'],
        'order_lines.*.user_id'            => ['required', 'integer'],
        'order_lines.*.quantity'           => ['required', 'integer', 'min:1'],
        'order_lines.*.price'              => ['required', 'numeric', 'min:0'],
        'order_lines.*.tax_percentage'     => ['required', 'numeric', 'min:0'],
    ];

    public function __construct(
        private CreateOrderHandler $createOrderHandler,
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

            $response = ($this->createOrderHandler)(
                CreateOrderCommand::create(
                    restaurantId:   $validated['restaurant_id'],
                    tableId:        $validated['table_id'],
                    openedByUserId: $validated['opened_by_user_id'],
                    closedByUserId: $validated['closed_by_user_id'] ?? null,
                    status:         $validated['status'],
                    diners:         $validated['diners'],
                    orderLinesData: $validated['order_lines'],
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}