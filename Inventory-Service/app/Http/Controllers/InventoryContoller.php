<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class InventoryContoller extends Controller
{

    public function index()
    {
        $JWT_content = JWT::parseToken()->getPayload();
        ////get the role of user, tenant or branch
        $role = JWTAuth::parseToken()->getPayload()->get('role');
        if($role == 'tenant'){
            $tenant_id = JWTAuth::parseToken()->getPayload()->get('tenant_id');

        }
        /////We get branch id
        $branch_id = JWTAuth::parseToken()->getPayload()->get('branch_id')?? JWTAuth::parseToken()->getPayload()->get('tenant_id');
        ////Now get the inventories
        if(JWTAuth::parseToken()->getPayload()->get('role') == 'branch'){
            $inventories = Inventory::where('branch_id', $branch_id)->get();
        }
        else{

            $inventories = Inventory::where('branch_id', $branch_id)->get();
        }
    }


    public function store(Request $request)
    {

    }


    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
