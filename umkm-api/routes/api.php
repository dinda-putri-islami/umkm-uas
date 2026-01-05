<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Tanpa Login)
|--------------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Wajib Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::put('products/{product}', [ProductController::class, 'update']);

});

/*
|--------------------------------------------------------------------------
| OWNER ONLY
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {

    Route::delete('products/{product}', [ProductController::class, 'destroy']);

});

/*
|-------------------------------------------------------------------------- 
| TRANSAKSI
|-------------------------------------------------------------------------- 
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('transactions', TransactionController::class)
        ->except(['edit', 'create']); // karena ini API, bukan web
});