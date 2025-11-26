<?php

namespace App\Http\Controllers;

use App\Repositories\TenantRepository;
use Illuminate\Http\Request;

class TenantBrancheController extends Controller
{
    private TenantRepository $tenant_repository;
    public function __construct(TenantRepository $tenant_repository)
    {
        $this->tenant_repository = $tenant_repository;
    }


    public function store(Request $request){
        return $this->tenant_repository->addBranch($request);
    }


    public function update($id, Request $request){
        return $this->tenant_repository->updateBranch($id, $request);
    }
}
