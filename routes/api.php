<?php

use App\Http\Controllers\AdTextController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\RankBoostController;
use App\Http\Controllers\SellTextController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/constants', [AccountController::class, 'getConstants']);
Route::post('/login', [AuthController::class, 'login']);


// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-password', [AuthController::class, 'updatePassword']);
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);

    // Account routes
 Route::prefix('accounts')->group(function () {
        Route::post('/', [AccountController::class, 'store']);
        Route::put('/{id}', [AccountController::class, 'update']);
        Route::delete('/{id}', [AccountController::class, 'destroy']);
        Route::post('/{id}/mark-for-deletion', [AccountController::class, 'markForDeletion']);
        Route::post('/{id}/restore', [AccountController::class, 'restore']);
        Route::post('/cleanup-expired', [AccountController::class, 'cleanupExpired']);
        Route::post('/upload-image', [AccountController::class, 'uploadImage']);
    });

    // Ad routes
    Route::prefix('ads')->group(function () {

        Route::post('/', [AdController::class, 'store']);
        Route::put('/{id}', [AdController::class, 'update']);
        Route::delete('/{id}', [AdController::class, 'destroy']);
        Route::post('/update-order', [AdController::class, 'updateOrder']);
    });

    // Rank boost routes
    Route::prefix('rank-boosts')->group(function () {

        Route::post('/', [RankBoostController::class, 'store']);
        Route::put('/{id}', [RankBoostController::class, 'update']);
        Route::delete('/{id}', [RankBoostController::class, 'destroy']);
    });

    Route::prefix('ad-texts')->group(function () {
        Route::post('/', [AdTextController::class, 'store']);
        Route::get('/{id}', [AdTextController::class, 'show']);
        Route::put('/{id}', [AdTextController::class, 'update']);
        Route::delete('/{id}', [AdTextController::class, 'destroy']);
    });

      Route::prefix('sell-texts')->group(function () {
        Route::post('/', [SellTextController::class, 'store']);
        Route::get('/{id}', [SellTextController::class, 'show']);
        Route::put('/{id}', [SellTextController::class, 'update']);
        Route::delete('/{id}', [SellTextController::class, 'destroy']);
    });

});

    Route::get('/ad-texts', [AdTextController::class, 'index']);
    Route::get('/sell-texts', [SellTextController::class, 'index']);


 Route::get('/ads', [AdController::class, 'index']);
 Route::get('/rank-boosts', [RankBoostController::class, 'index']);

  Route::get('/accounts', [AccountController::class, 'index']);
 Route::get('/accounts/{id}', [AccountController::class, 'show']);

