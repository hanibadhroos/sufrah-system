<?php

use App\Http\Controllers\AuthProxyController;
use App\Http\Controllers\OrderProxyController;
use App\Http\Controllers\TenantProxyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth.token')->group(function () {
    Route::get('/orders', [OrderProxyController::class, 'index']);
    Route::post('/orders', [OrderProxyController::class, 'store']);

    Route::get('/users', [AuthProxyController::class, 'index']);
    Route::post('/users', [AuthProxyController::class, 'store']);

    Route::get('/tenants', [TenantProxyController::class, 'index']);
    Route::post('/tenants', [TenantProxyController::class, 'store']);

    
});




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
