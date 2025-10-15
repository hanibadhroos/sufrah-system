<?php
namespace App\Interfaces;

use Illuminate\Http\Request;

interface AuthRepositoryInterface {
    // public function getAllUsers();
    // public function getUserById($id);
    // public function createUser(array $data);


    public function register(Request $request);
    public function login(Request $request);
    public function logout();
}
