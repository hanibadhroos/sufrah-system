<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInternalApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-API-KEY');
        if (!$key || $key !== config('services.internal_api_key')) {
            return response()->json(['error' => 'Unauthorized (invalid internal key)'], 401);
        }
        return $next($request);
    }

}
