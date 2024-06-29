<?php

use App\Http\Controllers\Api\Auth\AuthApiController;
use App\Http\Controllers\Api\User\UserApiController;
use Illuminate\Support\Facades\Route;

/**
 * |--------------------
 * | Public routes
 * |--------------------
 */
Route::post('register', [UserApiController::class, 'store'])->name('register');

/**
 * |------------------------
 * | Authentication routes
 * |------------------------
 */
Route::prefix('auth')->controller(AuthApiController::class)->group(function () {
    Route::post('login', 'login')->name('login');
});
