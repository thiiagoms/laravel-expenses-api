<?php

use App\Http\Controllers\Api\User\UserApiController;
use Illuminate\Support\Facades\Route;

/**
 * |--------------------
 * | Public routes
 * |--------------------
 */
Route::post('register', [UserApiController::class, 'store'])->name('register');
