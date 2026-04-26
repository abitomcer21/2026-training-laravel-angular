<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TimingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $method = $request->getMethod();
        $path = $request->getPathInfo();
        
        Log::info("MIDDLEWARE_START: {$method} {$path}");

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info("MIDDLEWARE_END: {$method} {$path} - {$duration}ms");

        $response->header('X-Response-Time', $duration . 'ms');
        
        return $response;
    }
}
