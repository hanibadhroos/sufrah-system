<?php

namespace App\Http\Controllers;

use App\Models\TenantBranch;
use App\Repositories\TenantRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function PHPSTORM_META\map;

class TenantController extends Controller
{
    private TenantRepository $tenant_Repo;

    public function __construct(TenantRepository $tenant_Repo)
    {
        $this->tenant_Repo = $tenant_Repo;
    }

    public function index(){
       return $this->tenant_Repo->index();
    }

    public function store(Request $request){

        $data = $request->validate([
            'name' => 'required',
            'type' => 'nullable|string',
            'email'=>'required|email',
            'password' => 'required|string|min:6',
            'logo'=>'required',
            'payment_method'=>'required|string',
            'location'=>'required',
            'status'=>'nullable',
            'cancel_cutoff_minutes'=>'required|string',
        ]);
        $data['id'] = Str::uuid();

        $userData= $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
            'phone'=> 'required',
        ]);

        $userData['password'] = Hash::make($request->password);
        $userData['id'] = Str::uuid();

        DB::beginTransaction();
        try{

            $data['password'] = Hash::make($request->password);

            /////Create Tenant
            $tenant = $this->tenant_Repo->create($data);

            ////Now we add this tenant to branches table.
            $branch = TenantBranch::create([
                'id'=>Str::uuid(),
                'name' => $tenant->name . 'Main branch',
                'location' => $tenant->location,
                'tenant_id' => $tenant->id,
                'phone' => $tenant->phone
            ]);

            if(!$branch){
                DB::rollBack();
                return response()->json(['error'=> 'Error while add branch'], 400);
            }

            /////Create Auth URL to add new user feald for this tenant.
            $userData['tenant_id'] = $tenant->id;
            $authUrl = config('services.auth_service' . '/api/register', 'http://127.0.0.1:8001' . '/api/register');
            $internalKey = config('services.internal_api_key');

            $response = Http::withHeaders([
                'X-API-KEY' => $internalKey,
                'Accept' => 'application/json'
            ])->post($authUrl,$userData);


            if($response->successful()){
                DB::commit();
                return response()->json([
                    'tenant'=> $tenant,
                    'user'=> $response->json('user')
                ]);
            }

            //// إذا فشل إنشاء المستخدم في Auth Service -> تراجع عن إنشاء التينانت
            DB::rollBack();

            return response()->json([
                'error'=> 'Failed to create user in Auth Service',
                'details'=>$response->json()
            ], $response->status() ?: 500);

        }
        catch(Exception $e){
            DB::rollBack();
            Log::error("Tenant Creation failed: " . $e->getMessage(), [
                'trace'=> $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);

        }
    }

    public function destroy($id, Request $request)
    {
        $token = $request->header('Authorization');
        return $this->tenant_Repo->destroy($id, $token);
    }

    public function update(Request $request, $id){

        return $this->tenant_Repo->update($request, $id);
    }

    public function profile(){
        return $this->tenant_Repo->profile();
    }

    public function updateProfile(Request $request, $id){

    }

    public function deleteBranch($id, Request $request){
        $token = $request->header('Authorization');
        return $this->tenant_Repo->deleteBranch($id, $token);
    }
}
