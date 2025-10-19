<?php

use App\Http\Controllers\TenantBrancheController;
use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth.jwt', 'admin'])->group(function(){
    //// Tenant APIs for admin
    Route::delete('/tenant/delete/{id}', [TenantController::class, 'destroy']);
    Route::get('/tenants/all',[TenantController::class, 'index']);
    Route::post('/tenants', [TenantController::class, 'store']);
});


////Tenant APIs for Tenant.
Route::middleware(['auth.jwt', 'tenant'])->group(function(){
    Route::put('/updateProfile/{id}', [TenantController::class, 'updateProfile']);
    Route::get('/profile', [TenantController::class, 'profile']);
    Route::put('/branche/update/{id}', [TenantBrancheController::class, 'update']);

});
