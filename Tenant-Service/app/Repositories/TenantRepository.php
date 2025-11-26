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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TenantRepository implements TenantRepositoryInterface {


    private $mainAuthUrl;
    private $internalKey;
    public function __construct()
    {
        $this->mainAuthUrl = config('services.auth_service');
        $this->internalKey = config('services.internal_api_key');

    }

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

    public function create(Request $request) {
        // return Tenant::create($data);
        
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
        $data['id'] = Str::uuid()->toString();

        $ownerData= $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
            'phone'=> 'required',
        ]);




        DB::beginTransaction();
        try{

            /////Create Tenant
            $data['password'] = Hash::make($request->password);
            $tenant =  Tenant::create($data);
            if(!$tenant){
                DB::rollBack();
                return response()->json(['error'=> 'create the tenant failed.'], 400);
            }


            ////Now we add this tenant to branches table.
            $branch = TenantBranch::create([
                'id'=>Str::uuid()->toString(),
                'name' => $tenant->name . 'Main branch',
                'email'=> $request->email,
                'password'=> Hash::make($request->password),
                'location' => $tenant->location,
                'tenant_id' => $tenant->id,
                'phone' => $tenant->phone,
                // 'owner_id' => $response->json()['id'],  //// We add it when the response for create owner into users table success .
            ]);
            if(!$branch){
                DB::rollBack();
                return response()->json(['error'=> 'Error while add branch'], 400);
            }
            ////Create a password, id, role, and tenant_id for the user which will crate.
            $ownerData['password'] = Hash::make($request->password);
            $ownerData['id'] = Str::uuid()->toString();
            $ownerData['role'] = 'tenant';
            $ownerData['tenant_id'] = $tenant->id;

            ////adding owner date in users table.
            /////Create Auth URL to add new user feald for this tenant.
            $authUrl = config('services.auth_service' . '/api/register', 'http://127.0.0.1:8001' . '/api/register');
            $internalKey = config('services.internal_api_key');
            $response = Http::withHeaders([
                'X-API-KEY' => $internalKey,
                'Accept' => 'application/json'
            ])->post($authUrl,$ownerData);

            if($response -> successful()){
                $owner_id = $response->json()['user']['original']['data']['user']['id'];
                //// الان بعدما انشانا الميستخدم نقوم بتحديث التينانت السابق واضافة له وونر ايدي
                $tenant_for_update =  Tenant::where('id',$tenant->id)->update(['owner_id' =>$owner_id ]);

                if(!$tenant_for_update){
                    return response()->json(['error'=> 'can not add owner id to this tenant --> '], 400);
                }

                ///// Now add owner id to the previous tenant_branches 
                $branch_for_update =  TenantBranch::where('id',$branch->id)->update(['owner_id' =>$owner_id]);
                if(!$branch_for_update){
                    DB::rollBack();
                    return response()->json('error ==> While add owner id to branch for this tenant.', 500);
                }
            }
            else{
                DB::rollBack();
                return response()->json('error while register the user for this tenant ===> ' . $response->json()['message']);
            }

            //// إذا فشل إنشاء المستخدم في Auth Service -> تراجع عن إنشاء التينانت
            DB::commit();

            return response()->json([
                'message'=> 'create tenant successfully',
                'owner'=>$response->json(),
                'tenant' => $tenant,
            ], $response->status() ?: 200);

        }
        catch(Exception $e){
            DB::rollBack();
            Log::error("Tenant Creation failed: " . $e->getMessage(), [
                'trace'=> $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);

        }
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



    /////Branch management.
    public function addBranch(Request $request){
        try{
            // echo $request; exit;
            $validated = $request->validate([
                'name' => 'required|string',
                'password' => 'required|string|min:8',
                'email' => 'required',
                'location' => 'required',
                'phone' => 'required',
                'branch_tenant_id' => 'required'
            ]);

            $validated['password'] = Hash::make($request->password);
            $validated['tenant_id'] = $request->branch_tenant_id;
            $validated['id'] = Str::uuid()->toString();
            $validated['owner_id'] = Tenant::where('id' ,$request->branch_tenant_id)->value('owner_id');
            DB::beginTransaction();
            $branch = TenantBranch::create($validated);
            if($branch){
                ////Then we add new user for it 
                $url = config('services.auth_service' . '/api/register', 'http://127.0.0.1:8001' . '/api/register');
                $internalKey = config('services.internal_api_key');
                $userData = $validated;
                $userData['user_branch_id'] = $branch->id;
                // print_r($userData); exit;
                $userData['role'] = 'branch';

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
            return response()->json(['error' => 'Error while add new branch ' . $e->getMessage(), 'Trace' => $e->getTraceAsString()], 500);
        }
    }
    public function deleteBranch($id, $token){
        try{
            DB::beginTransaction();
            $branch = TenantBranch::find($id)->delete();
            if($branch){

                /////Delete The User using Tenant Id.
                $authUrl = config('services.auth_service') . '/api/user/delete';
                $response = Http::withHeaders([
                    'Authorization' => $token,
                    'X-API-KEY' => $this->internalKey,
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
    public function updateBranch($id, Request $request){
         $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required',
            'phone' => 'required',
            'email'=> 'required|email',
            'password' => 'required',
        ]);

        try{
            DB::beginTransaction();


            $branch = TenantBranch::find($id);
            $branch->update([
                'name'=> $request->name,
                'location'=> $request->location,
                'phone'=> $request->phone,
            ]);
            $branch->save();

            $token = $request->bearerToken();
            $url = config('services.auth_service' , 'http://127.0.0.1:8001' );
            ////Then we update users table which owner this branch. ----> update email and password.
            $owner_id = $branch->owner_id;
            //// Response code for update  user.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-API-KEY' => $this->internalKey,
                'Accept' => 'application/json',
            ])->put($url . '/api/user/' . $owner_id, ['email'=> $request->email, 'password'=> $request->password]);

            if($response->successful()){
                DB::commit();
                return response()->json(['message' => 'Branch updated successfully.', 'Branch'=> $branch, 'user' => $response->json()], 201);
            }
            else{
                DB::rollBack();
                return response()->json(['error ' => 'Error while update branche user -->'. $response->reason()], $response->status());
            }

            // if($branch){
            //     //// Then update its data into users table.
            //     $userUrl = $this->mainAuthUrl . '/user-by-branche/' . $id;
            //     $user_id = Http::withHeaders(
            //         [
            //             'Authorization' => 'Bearer' .  JWTAuth::getToken(),
            //             'X-API-KEY' => $this->internalKey,
            //             'Accept' => 'application/json',
            //         ])->get($userUrl);
            //     $token = JWTAuth::getToken();
            //     $url = $this->mainAuthUrl . '/user/' . $user_id;

            //     //// Response code for update  user.
            //     $response = Http::withHeaders([
            //         'Authorization' => 'Bearer' . $token,
            //         'X-API-KEY' => $this->internalKey,
            //         'Accept' => 'application/json',
            //     ])->withUrlParameters([
            //             'user_id'=>$user_id
            //     ])->put($url . '/user/{user_id}',$validated);

            //     if($response->successful()){
            //         DB::commit();
            //     }
            //     else{
            //         DB::rollBack();
            //         return response()->json(['error ' => 'Error while update branche user -->'. $response->reason()], $response->status());
            //     }

            // }
        }
        catch(Exception $e){
            DB::rollBack();
            return response()->json(['error' => 'Error while update branch ' . $e->getMessage()], 400);
        }
    }
}
