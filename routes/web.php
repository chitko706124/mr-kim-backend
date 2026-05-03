<?php

use App\Http\Controllers\Api\AccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/constants', [AccountController::class, 'getConstants']);
//   Route::get('/accounts', [AccountController::class, 'index']);
