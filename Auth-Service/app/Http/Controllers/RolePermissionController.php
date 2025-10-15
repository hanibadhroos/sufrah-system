<?php

namespace App\Http\Controllers;

use App\Repositories\RolePermissionRepository;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    private RolePermissionRepository $role_permission_repository;

    public function __construct(RolePermissionRepository $role_permission_repository)
    {
        $this->role_permission_repository = $role_permission_repository;
    }
    public function index(){
        return $this->role_permission_repository->index();
    }

    public function addRole(Request $request){
        return $this->role_permission_repository->addRole($request);
    }

    public function updateRole(Request $request, $id){
        return $this->role_permission_repository->updateRole($request, $id);
    }
    public function deleteRole($id){
        return $this->role_permission_repository->deleteRole($id);
    }


    public function addPermission(Request $request){
        return $this->role_permission_repository->addPermission($request);
    }

    public function updatePermission(Request $request, $id){
        return $this->role_permission_repository->updatePermission($request, $id);
    }
    public function deletePermission($id){
        return $this->role_permission_repository->deletePermission($id);
    }



}
