<?php

namespace App\Http\Middleware;

use Closure;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticateToken
{
    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::parseToken(); 
            $user = $token->authenticate();

            // نمرر التوكن واليوزر للـ request
            $request->merge([
                'auth_user' => $user,
                'jwt_token' => (string) $token->getToken(),
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Unauthorized - Invalid or Expired Token',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }

}
