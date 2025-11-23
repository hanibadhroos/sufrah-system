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
       return $this->tenant_Repo->create($request);
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
