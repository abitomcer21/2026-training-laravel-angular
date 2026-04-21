<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Products\Application\CreateProduct\CreateProduct;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateProduct $createProduct,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Aceptar tanto UUIDs como integers para family_id y tax_id
        $validated = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'family_id' => ['required'],  // Puede ser UUID o integer
            'tax_id' => ['required'],     // Puede ser UUID o integer
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_src' => ['nullable', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);

        $familyId = $validated['family_id'];
        $taxId = $validated['tax_id'];

        // Si es UUID (string con formato UUID), buscar el ID entero
        if (is_string($familyId) && strlen($familyId) > 10) {
            $familyModel = EloquentFamily::where('uuid', $familyId)->first();
            if (!$familyModel) {
                return new JsonResponse(['message' => 'Family not found'], 404);
            }
            $familyId = $familyModel->id;
        }

        // Si es UUID (string con formato UUID), buscar el ID entero
        if (is_string($taxId) && strlen($taxId) > 10) {
            $taxModel = EloquentTax::where('uuid', $taxId)->first();
            if (!$taxModel) {
                return new JsonResponse(['message' => 'Tax not found'], 404);
            }
            $taxId = $taxModel->id;
        }

        // Si image_src es null o vacío, enviarlo como null (se guardará NULL en BD)
        $imageSrc = $validated['image_src'] ?? null;

        $response = ($this->createProduct)(
            (int) $familyId,
            (int) $taxId,
            $validated['restaurant_id'],
            $validated['name'],
            $validated['price'],
            $validated['stock'],
            $imageSrc,
            $validated['active'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
