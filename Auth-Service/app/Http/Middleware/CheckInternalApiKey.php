<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInternalApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-API-KEY');
        if (!$key || $key !== config('services.internal_api_key')) {
            return response()->json(['error' => 'Unauthorized (invalid internal key)'], 401);
        }
        return $next($request);
    }
}
