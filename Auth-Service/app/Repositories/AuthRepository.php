<?php
namespace App\Repositories;

use App\Models\User;
use App\Interfaces\AuthRepositoryInterface;
use App\Models\TenantBranch;
use App\Services\HttpClientService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthRepository implements AuthRepositoryInterface
{
    public function __construct(private HttpClientService $httpClient) {}

    // تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'phone'=> 'required|string',
            'tenant_id'=>'nullable|string',
            'branch_id'=>'nullable|string',
            'customer_id'=>'nullable|string',
        ]);

        // $data['password'] = Hash::make(trim($data['password']));
        // $data['id'] = Str::uuid();


        $user = User::create([
            'id'        => Str::uuid(),
            'name'      => $request->name,
            'email'     => $request->email,
            'password' => $request->password,
            'role'      => $request->role,
            'phone'     => $request->phone,
            'tenant_id' => $request->tenant_id,
            'branch_id' => $request->branch_id,
            'customer_id' => $request->customer_id,
        ]);

        ////We get branch id from branches table using tenant id for add it into token.
        $branch_id = TenantBranch::where('tenant_id', $request->tenant_id)->value('id');
        ////If role = tenant then create token with role and tenant id.
        if($request->role == 'tenant'){
            $token = JWTAuth::claims([
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
                'branch_id' => $branch_id,
            ])->fromUser($user);
        }

        ////If role = branch then create token with role and customer id.
        elseif($request->role == 'branch'){
            $token = JWTAuth::claims([
                'role' => $user->role,
                'branch_id' => $user->branch_id,
            ])->fromUser($user);
        }

        ////If role = customer then create token with role and customer id.
        elseif($request->role == 'customer'){
            $token = JWTAuth::claims([
                'role' => $user->role,
                'customer_id' => $user->tenant_id,
            ])->fromUser($user);
        }

        $token = JWTAuth::claims([
            'role' => $user->role,

        ])->fromUser($user);

        return response()->json([
            'meta' => [
                'code' => 201,
                'status' => 'success',
                'message' => 'User registered successfully'
            ],
            'data' => [
                'user' => $user,
                'token' => $token

            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $ttl = config('jwt.ttl') ?? 0; // لو 0 معناها ما ينتهي

        // جلب المستخدم الحالي
        $user = auth()->user();

        ////If role = tenant then create token with role and tenant id.
        if($request->role == 'tenant'){

            $token = JWTAuth::claims([
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
                'id' => $user->id,
            ])->fromUser($user);
        }

        ////If role = customer then create token with role and customer id.
        elseif($request->role == 'branch'){
            $token = JWTAuth::claims([
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'id' => $user->id,
            ])->fromUser($user);
        }

        ////If role = customer then create token with role and customer id.
        elseif($request->role == 'customer'){
            $token = JWTAuth::claims([
                'role' => $user->role,
                'customer_id' => $user->tenant_id,
                'id' => $user->id,
            ])->fromUser($user);
        }

        $token = JWTAuth::claims([
            'role' => $user->role,

        ])->fromUser($user);

        // توليد توكن مع role
        // $token = JWTAuth::claims([
        //     'role' => $user->role,
        //     'id' => $user->id,
        // ])->fromUser($user);


        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttl > 0 ? $ttl * 60 : null, // ثواني أو null لو غير منتهي
        ]);
    }


    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to logout, token missing or invalid'], 400);
        }
    }

    public function delete($id){
        try{
            $user = User::where('id', $id)->delete();
            if($user){
                return response()->json(['message'=>'User deleted successfully.'], 200);
            }
            else{
                return response()->json(['error'=>'User not found.'], 400);
            }
        }
        catch(Exception $e){
            return response()->json(['error'=>'Error while delete the user ' . $e->getMessage()], 400);
        }
    }
}

