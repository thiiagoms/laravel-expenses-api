<?php

use App\Http\Controllers\Api\Auth\AuthApiController;
use App\Http\Controllers\Api\Expense\ExpenseApiController;
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

/**
 * |------------------------
 * | Protected routes
 * |------------------------
 */
Route::middleware(['auth:api'])->group(function (): void {

    Route::prefix('user')->name('user.')->controller(UserApiController::class)->group(function () {
        Route::get('', 'getUser')->name('me');
        Route::patch('', 'update')->name('update');
        Route::put('', 'update');
    });

    /**
     * |----------------------------------
     * | Expense routes
     * |----------------------------------
     */
    Route::apiResource('expense', ExpenseApiController::class);
});
