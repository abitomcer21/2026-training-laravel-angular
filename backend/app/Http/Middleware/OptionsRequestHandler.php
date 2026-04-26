<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptionsRequestHandler
{
    public function handle(Request $request, Closure $next)
    {
        // Si es una request OPTIONS (preflight CORS), responde inmediatamente
        if ($request->isMethod('OPTIONS')) {
            return response()
                ->json([], 200)
                ->header('Access-Control-Allow-Origin', env('FRONTEND_URL', 'http://localhost:4200'))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                ->header('Access-Control-Max-Age', 3600);
        }

        return $next($request);
    }
}
