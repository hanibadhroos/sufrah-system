<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Contracts\Role;

Route::post('/register', [AuthController::class, 'store'])
     ->middleware('internal.api');
/////Login Route
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth.jwt')->group(function(){
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/user/{user_id}', [UserController::class, 'update']);
});



Route::middleware(['auth.jwt', 'admin'])->group(function () {
    Route::delete('/user/delete', [UserController::class, 'delete']);
    Route::get('/user/all', [UserController::class, 'allUsers']);
    /////Get user by tenant id
    Route::get('/user-by-branche/{branche_id}', [UserController::class, 'getUserByBrancheId']);

    ////Role And permission APIs
    Route::controller(RolePermissionController::class)->group(function(){
        /////Roles APIs
        Route::get('/allRoles', 'index');
        Route::post('/addRole', 'addRole');
        Route::put('/updateRole/{id}', 'updateRole');
        Route::delete('/deleteRole/{id}', 'deleteRole');

        ////Permissions APIs
        Route::post('/addPermission', 'addPermission');
        Route::put('/updatePermission/{id}', 'updatePermission');
        Route::delete('/deletePermission/{id}', 'deletePermission');
    });

});



Route::get('/',[AuthController::class, 'hello']);







Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
;
