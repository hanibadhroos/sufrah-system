<?php
namespace App\Interfaces;

use Illuminate\Http\Request;

interface UserRepositoryInterface{

    public function delete(Request $request);
    public function allUsers();
}
