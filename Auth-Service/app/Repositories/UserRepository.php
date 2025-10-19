<?php
namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserRepository implements UserRepositoryInterface {
    public function delete(Request $request){
        try{

            ////Delete user by Tenant id.
            if($request->has('tenantId')){
                $tenant_id  = $request->get('tenantId');
                $user = User::where('tenant_id', $tenant_id)->delete();
                if($user){
                    return response()->json(['message'=>'User deleted successfully.'], 200);
                }
                else{
                    return response()->json(['error'=>'User not found.'], 400);
                }
            }

            elseif($request->has('branch_id')){
                $branch_id  = $request->get('branchId');
                $user = User::where('branch_id', $branch_id)->delete();
                if($user){
                    return response()->json(['message'=>'User deleted successfully.'], 200);
                }
                else{
                    return response()->json(['error'=>'User not found.'], 400);
                }
            }

            ////Else delete user by user id.
            else{
                $id = $request->get('id');
                $user = User::find($id)->delete();
                if($user){
                    return response()->json(['message'=>'User deleted successfully.'], 200);
                }
                else{
                    return response()->json(['error'=>'User not found.'], 400);
                }
            }


        }
        catch(Exception $e){
            return response()->json(['error'=>'Error while delete the user ' . $e->getMessage()], 400);
        }
    }

    public function allUsers()
    {
        try{
            $users = User::all();
            if($users){
                return response()->json(['user'=> $users], 200);
            }
            return response()->json(['error'=>'There are not users.'], 200);
        }
        catch(Exception $e){
            return response()->json(['error'=> 'Error while get all users ---> ' . $e->getMessage()], 400);
        }
    }

    ////Get user by tenant id
    public function getUserByBrancheId($tenant_id){
        try{
            $user = User::where('tenant_id', $tenant_id)->first();
            if($user){
                return response()->json($user, 200);
            }
            return response()->json(['error'=>'user not found'], 400);
        }
        catch(Exception $e){
            return response()-> json(['error' => $e->getMessage()], 500);
        }
    }
}
