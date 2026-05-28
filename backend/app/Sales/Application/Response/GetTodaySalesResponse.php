<?php

namespace App\Sales\Application\Response;

final readonly class GetTodaySalesResponse
{
    private function __construct(
        private array $data,
        private string $message,
    ) {}

    public static function create(array $sales): self
    {
        return new self(
            data:    $sales,
            message: 'Ventas del día obtenidas correctamente',
        );
    }

    public function toArray(): array
    {
        return [
            'data'    => $this->data,
            'message' => $this->message,
        ];
    }
}
