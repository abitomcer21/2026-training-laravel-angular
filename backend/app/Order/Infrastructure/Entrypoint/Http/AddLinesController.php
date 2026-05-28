<?php

namespace App\Order\Infrastructure\Entrypoint\Http;

use App\Order\Application\Command\AddOrderLinesCommand;
use App\Order\Application\Handler\AddOrderLinesHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class AddLinesController
{
    public function __construct(
        private AddOrderLinesHandler $addOrderLinesHandler,
    ) {}

    private function validationRules(): array
    {
        return [
            'order_lines'                  => ['required', 'array', 'min:1'],
            'order_lines.*.product_id'     => ['required', 'string'],
            'order_lines.*.user_id'        => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! is_string($value) && ! is_int($value)) {
                        $fail('The '.$attribute.' field must be a string or integer.');
                    }
                },
            ],
            'order_lines.*.quantity'       => ['required', 'integer', 'min:1'],
            'order_lines.*.price'          => ['required', 'numeric', 'min:0'],
            'order_lines.*.tax_percentage' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function __invoke(Request $request, string $orderId): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $response = ($this->addOrderLinesHandler)(
                AddOrderLinesCommand::create(
                    orderId:        $orderId,
                    orderLinesData: $validator->validated()['order_lines'],
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
