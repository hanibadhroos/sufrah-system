<?php

namespace App\Http\Middleware;

use Closure;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticateToken
{
    public function handle($request, Closure $next)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();

            $request->merge([
                'jwt_token' => (string) JWTAuth::getToken(),
                'userId'=> $payload->get('id'),
            ]
        );

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Unauthorized - Invalid or Expired Token',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
