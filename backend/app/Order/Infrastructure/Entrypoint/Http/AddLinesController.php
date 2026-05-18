<?php

namespace App\Order\Infrastructure\Entrypoint\Http;

use App\Order\Application\AddOrderLines\AddOrderLines;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AddLinesController
{
    public function __construct(
        private AddOrderLines $addOrderLines,
    ) {}

    public function __invoke(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'order_lines' => ['required', 'array', 'min:1'],
            'order_lines.*.product_id' => ['required', 'string'],
            'order_lines.*.user_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! is_string($value) && ! is_int($value)) {
                        $fail('The '.$attribute.' field must be a string or integer.');
                    }
                },
            ],
            'order_lines.*.quantity' => ['required', 'integer', 'min:1'],
            'order_lines.*.price' => ['required', 'numeric', 'min:0'],
            'order_lines.*.tax_percentage' => ['required', 'numeric', 'min:0'],
        ]);

        $response = ($this->addOrderLines)(
            $orderId,
            $validated['order_lines'],
        );

        return new JsonResponse($response->toArray(), 200);
    }
}
