<?php
namespace App\Interfaces;

use Illuminate\Http\Request;

interface RolePermissionRepositoryInterface {

    public function index();
    public function addRole(Request $request);
    public function updateRole(Request $request, $id);
    public function deleteRole(Request $request);

    public function addPermission(Request $request);
    public function deletePermission($id);
}

