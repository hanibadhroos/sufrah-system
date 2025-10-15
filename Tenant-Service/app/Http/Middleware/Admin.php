<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{
            $role = JWTAuth::parseToken()->getPayload()->get('role');
            if($role !== 'admin'){
                return response()->json(['error'=> 'Access denied, Admins only.'], 403);
            }

        }
        catch(Exception $e){
            return response()->json(['error'=> 'Unauthorized', 'message'=> $e->getMessage()],401);
        }
        return $next($request);
        
    }
}
