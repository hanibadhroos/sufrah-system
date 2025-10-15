<?php
namespace App\Repositories;

use App\Interfaces\RolePermissionRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RolePermissionRepository implements RolePermissionRepositoryInterface{

    public function index()
    {
        try{
            $roles = Role::all();
            $permissions = Permission::all();
            if($roles){
                return response()->json(['Roles'=>$roles, 'Permissions' =>$permissions], 200);
            }
            else{
                return response()->json(['error'=> 'There are not any Roles.']);
            }
        }
        catch(Exception $e){
            return response()->json(['error'=>'Error while get all roles ' . $e->getMessage()], 400);
        }
    }

    public function addRole(Request $request)
    {
        try{
            $validated = $request->validate([
                'name'=> 'required|string',
            ]);
            $validated['id'] = Str::uuid();
            $validated['guard_name'] = 'api';

            $role = Role::create($validated);

            if($role){
                return response()->json(['role created successfully -->'=> $role], 201);
            }
            return response()->json(['error'=>'create role failed'], 400);
        }
        catch (Exception $e){
            return response()->json('Error while create new role '. $e->getMessage(), 400);
        }
    }

    public function updateRole(Request $request, $id){

        try{
            $validated = $request->validate([
                'name'=> 'required|string',
            ]);

            $validated['id'] = Str::uuid();
            $validated['guard_name'] = 'api';

            $role = Role::find($id)->update($validated);
            if($role){
                return response()->json('Role update successfully', 200);
            }

            return response()->json('Can not update this role', 400);
        }
        catch(Exception $e){
            return response()->json(['error'=>'Error while update Role ' . $e->getMessage()], 400);
        }
    }

    public function deleteRole($id)
    {
        try{
            $role = Role::find($id)->delete();
            if($role){
                return response()->json('role deleted successfully', 200);
            }
            return response()->json('can not delete this role');
        }
        catch(Exception $e){
            return response()->json('Error whiel delete the role ' . $e->getMessage());
        }
    }

    public function addPermission(Request $request){
        try{
            $validated = $request->validate([
                'name'=>'required|string',
                // 'guard_name'=>'required|string'
            ]);

            $validated['id'] = Str::uuid();
            $validated['guard_name']='api';

            $permission = Permission::create($validated);
            if($permission){
                return response()->json('Permission created successfully', 201);
            }
            return response()->json(['error'=> 'Can not add new permission'], 400);
        }
        catch(Excption $e){
            return response(['error'=>'Error while add new permission '. $e->getMessage()]);
        }


    }

    public function updatePermission(Request $request, $id){

        try{
            $validated = $request->validate([
                'name'=>'required|string'
            ]);

            $permission = Permission::find($id)->update($validated);

            if($permission){
                return response()->json(['Permission updated successfully --> '. $permission ], 201);
            }
            return response()->json(['error'=> 'Can not update this permission'], 400);
        }
        catch(Exception $e){
            return response()->json(['error'=>'Error while update the permission ' . $e->getMessage()], 400);
        }


    }
    public function deletePermission($id){
        try{
            $permission = PerMIssion::find($id)->delete();

            if($permission){
                return response()->json('Permission deleted successfully', 200);
            }
            return response()->json('Can not Delete this permission', 400);
        }

        catch(Exception $e){
            return response()->json(['error'=> 'Error while delete the permission '. $e->getMessage()],400);
        }
    }
}

