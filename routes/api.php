<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logger', [LogController::class, 'save_log']);
    Route::get('role', [UserController::class, 'role_user']);
    Route::post('alluser', [UserController::class, 'all_user']);
    Route::get('user_profile/{id}', [UserController::class, 'user_profile']);
    Route::patch('user_profile/update/{id}', [UserController::class, 'user_update']);
    Route::delete('user_profile/destroy/{id}', [UserController::class, 'user_destroy']);
    Route::get('user', [UserController::class, 'user_by_id']);

    Route::group(['prefix' => 'dashboard'], function () {
        Route::post('store', [DashboardController::class, 'store']);
        Route::get('list', [DashboardController::class, 'list_template']);
    });
});

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
