<?php

namespace App\Http\Controllers;

use App\Repositories\AuthRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PhpParser\Node\Stmt\Else_;

class AuthController extends Controller
{

    private AuthRepository $auth_repository;

    public function __construct(AuthRepository $auth_repository)
    {
        $this->auth_repository = $auth_repository;
    }

    public function store(Request $request){

        try{
            
            $user = $this->auth_repository->register($request);

            if($user){
                return ['message'=>'User created successfully', 'user'=>$user];
            }
        }

        catch(Exception $e){
            Log::error("user creation failed: " . $e->getMessage(),[
                'trace' => $e->getTraceAsString()
            ]);

            return ['error'=>'Register Error', 'message' => $e->getMessage()];
        }
    }

    public function login(Request $request)
    {
        return $this->auth_repository->login($request);
    }

    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is invalid or expired'], 401);
        }
    }

    public function delete(Request $request){
        $userId = $request->get('userId');
        return $this->auth_repository->delete($userId);
    }

}
