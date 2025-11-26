<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserRepository $user_repository;
    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }
    public function delete(Request $request){

        return $this->user_repository->delete($request);
    }

    public function update($user_id, Request $request){
        $validated = $request->validate([
            'email'=>'required|email',
            'password' => 'required|max:8'
        ]);
        $user = User::where('id' ,$user_id)->first();
        $user->update($validated);
        return response()->json( ['message'=>'user updated successfully.', 'user' => $user]);
    }

    public function allUsers(){
        return $this->user_repository->allUsers();
    }

    /////Get user by tenant id
    public function getUserByBrancheId($tenant_id){
        return $this->user_repository->getUserByBrancheId($tenant_id);
    }
}
