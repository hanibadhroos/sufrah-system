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

    public function allUsers(){
        return $this->user_repository->allUsers();
    }

}
