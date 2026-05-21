<?php

namespace App\Shared\Infrastructure\Entrypoint\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadImageController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('image')->store('images', 'public');

        return new JsonResponse(['path' => $path], 201);
    }
}