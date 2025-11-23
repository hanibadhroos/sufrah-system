<?php
namespace App\Interfaces;

use Illuminate\Http\Request;

interface TenantRepositoryInterface {
    public function profile();
    public function create(Request $request);
    public function find($id);
    public function destroy($id, $token);
    public function index();
    public function update(Request $request, $id);

    public function updateProfile(Request $request, $id);


    public function allBranches();
    public function addBranch(Request $request);
    public function deleteBranch($id, $token);
    public function updateBranch(Request $request, $id);
    
}