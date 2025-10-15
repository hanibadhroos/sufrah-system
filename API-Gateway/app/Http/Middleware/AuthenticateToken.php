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
            // محاولة التحقق من التوكن واستخراج المستخدم
            $user = JWTAuth::parseToken()->authenticate();
            $request->merge(['auth_user' => $user]); // تضيف المستخدم للطلب
        } catch (JWTException $e) {
            return response()->json(['error' => 'Unauthorized - Invalid or Expired Token'], 401);
        }

        return $next($request);
    }
}
