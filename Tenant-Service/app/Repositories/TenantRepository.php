<?php
namespace App\Repositories;

use App\Interfaces\TenantRepositoryInterface;
use App\Models\Tenant;
use App\Models\TenantBranch;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TenantRepository implements TenantRepositoryInterface {

    public function profile(){
        try{

            ///// First we get tenant id using user id of token
            $tenant_id = JWTAuth::parseToken()->getPayload()->get('tenant_id');
            $tenant = Tenant::find($tenant_id);

            if($tenant){
                return response()->json(['data'=> $tenant], 200);
            }
            return response()->json(['error'=>'Failed to get your profile, Tenant not found'],400);
        }
        catch(Exception $e){
            return response()->json('Error while get your profile ' . $e->getMessage(), 400);
        }
    }

    public function index(){
        $tenants = Tenant::all();
        if($tenants){
            return response()->json(['tenants'=>$tenants], 201);
        }
        else{
            return response()->json(['error'=>'There is not any tenant.'],400);
        }
    }

    public function create(array $data) {
        return Tenant::create($data);
    }
    public function find($id) {
        return Tenant::find($id);
    }
    public function destroy($id, $token)
    {
        try{
            DB::beginTransaction();

            ////Delete the Tenant.
            $tenant = Tenant::find($id);
            if (!$tenant) {
                return response()->json(['message' => 'Tenant not found'], 404);
            }
            $tenant->delete();


            /////Delete The User using Tenant Id.
            // $userId = $userId;
            $authUrl = config('services.auth_service') . '/api/user/delete';
            $internalKey = config('services.internal_api_key');

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-API-KEY' => $internalKey,
                'Accept' => 'application/json',
            ])->post($authUrl, ['tenantId' => $id]);

            if ($response->successful()) {
                DB::commit();
                return response()->json(['message' => 'Tenant deleted successfully']);
            }

            DB::rollBack();
            return response()->json([
                'error' => 'Failed to delete Tenant',
                'details' => $response->json(),
            ], $response->status() ?: 500);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error while deleting tenant: ' . $e->getMessage()]);
        }
    }
    public function update(Request $request , $id){
        try{
            $tenant = Tenant::where('id', $id)->update($request);
            if(!$tenant){
                return response()->json(['error'=>'Tenant not found'], 400);
            }
            return response()->json(['message'=>'Tenant updated successfully.'], 201);
        }
        catch( Exception $e){
            return response()->json(['error'=>"Error while update tenant ". $e->getMessage()]);
        }
    }

    public function updateProfile(Request $request, $id){
        try{
            $validated = $request->validate([
                'name'=>'required|string',
                'phone' => 'required',
                'logo'=> 'requird',
                'payment_method'=> 'requird',
                'location' => 'requird',
                'cancel_cutoff_minutes' => 'required',
            ]);

        }
        catch(Exception $e){
            return response()->json(['error' => 'Error while update your profile ' . $e->getMessage()], 400);
        }
    }

    public function allBranches(){
        try{

            ////First we get tenant id of Token.
            $tenant_id = JWTAuth::parseToken()->getPayload()->get('tenant_id');
            $branch =  TenantBranch::where('tenant_id', $tenant_id)->get();
            if($branch){
                return response()->json(['data'=>$branch], 200);
            }
            return response()->json(['error' => 'There are not any branches']);
        }   
        catch(Exception $e){
            return response()->json(['error'=>'Error while get your barnches ' . $e->getMessage()], 400);
        }
    }

    public function addBranch(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'password' => 'required|string|min:6',
                'role' => 'nullable|string',
                'location' => 'required',
                'phone' => 'required',
            ]);

            $validated['id'] = Str::uuid();
            $validated['tenant_id'] = JWTAuth::parseToken()->getPayload()->get('tenant_id');
            DB::beginTransaction();
            $branch = TenantBranch::create($validated);
            if($branch){
                ////Then we add new user for it 
                $url = config('services.auth_service' . '/api/register', 'http://127.0.0.1:8001' . '/api/register');
                $internalKey = config('services.internal_api_key');
                $userData = $validated;

                $response = Http::withHeaders([
                    'X-API-KEY' => $internalKey,
                    'Accept' => 'application/json'
                ])->post($url,$userData);

                if($response->successful()){
                    DB::commit();
                    return response()->json([
                        'data'=> $branch,
                        'user'=> $response->json('user')
                    ], 201);
                }
                DB::rollBack();
                return response()->json([
                    'error'=> 'Failed to create user in Auth Service',
                    'details'=>$response->json()
                ], $response->status() ?: 500);
            }
        }
        catch(Exception $e){
            return response()->json(['error' => 'Error while add new branch ' . $e->getMessage()], 400);
        }
    }
    public function deleteBranch($id, $token){
        try{
            DB::beginTransaction();
            $branch = TenantBranch::find($id)->delete();
            if($branch){
                
                /////Delete The User using Tenant Id.
                $authUrl = config('services.auth_service') . '/api/user/delete';
                $internalKey = config('services.internal_api_key');
                $response = Http::withHeaders([
                    'Authorization' => $token,
                    'X-API-KEY' => $internalKey,
                    'Accept' => 'application/json',
                ])->post($authUrl, ['branchId' => $id]);

                if($response->successful()){
                    DB::commit();
                    return response()->json('Branch deleted successfully.' , 200);
                }


                DB::rollBack();
                return response()->json([
                    'error' => 'Failed to delete Branch',
                    'details' => $response->json(),
                ], $response->status() ?: 500);
            }
        }
        catch(Exception $e){
            return response()->json(['error' => 'Error while delete the branch ' . $e->getMessage()], 400);
        }
    }
    public function updateBranch(Request $request, $id){
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required',
            'phone' => 'required',
        ]);

        try{
            DB::beginTransaction();
            $branch = TenantBranch::find($id)->update($validated);
            if($branch){
                //// Then update its data into users table.
                // $user = 
            }
        }        
        catch(Exception $e){
            return response()->json(['error' => 'Error while update branch ' . $e->getMessage()], 400);
        }
    }
}
