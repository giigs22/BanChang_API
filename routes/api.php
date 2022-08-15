<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WidgetController;
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
    Route::post('setting/update', [SettingController::class, 'update']);
    Route::get('setting/list', [SettingController::class, 'getlist']);

    Route::group(['prefix' => 'users'], function () {
        Route::post('alluser', [UserController::class, 'all_user']);
        Route::get('profile/{id}', [UserController::class, 'user_profile']);
        Route::patch('profile/update/{id}', [UserController::class, 'user_update']);
        Route::delete('profile/destroy/{id}', [UserController::class, 'user_destroy']);
        Route::get('user', [UserController::class, 'user_by_id']);
    });
    Route::group(['prefix' => 'roles'], function () {
        Route::get('role', [RoleController::class, 'role_user']);
        Route::post('allrole', [RoleController::class, 'all_role']);
        Route::post('store', [RoleController::class, 'store']);
        Route::get('{id}', [RoleController::class, 'role_by_id']);
        Route::patch('update/{id}', [RoleController::class, 'update']);
        Route::post('destroy', [RoleController::class, 'destroy']);
    });

    Route::group(['prefix' => 'dashboard'], function () {
        Route::post('store', [DashboardController::class, 'store']);
        Route::post('list', [DashboardController::class, 'list_template']);
        Route::get('template/{id}', [DashboardController::class, 'template_by_id']);
        Route::patch('update/{id}', [DashboardController::class, 'update']);
        Route::post('destroy', [DashboardController::class, 'destroy']);
        Route::get('group_user_temp', [DashboardController::class, 'group_user_temp']);
        Route::get('user_temp', [DashboardController::class, 'user_temp']);
        Route::post('update_group', [DashboardController::class, 'update_group']);
        Route::post('update_user', [DashboardController::class, 'update_user']);
    });

    Route::group(['prefix' => 'widget'], function () {
        Route::post('list', [WidgetController::class, 'list_widget']);
        Route::get('listall', [WidgetController::class, 'list_widget_all']);
        Route::post('addcate', [WidgetController::class, 'addcate']);
        Route::post('updatecate', [WidgetController::class, 'updatecate']);
        Route::post('destroy', [WidgetController::class, 'destroy']);
    });

    Route::group(['prefix' => 'device'], function () {
        Route::post('store', [DeviceController::class, 'store']);
        Route::post('list', [DeviceController::class, 'list_device']);
        Route::get('{id}', [DeviceController::class, 'device_by_id']);
        Route::patch('update/{id}', [DeviceController::class, 'update']);
        Route::delete('destroy/{id}', [DeviceController::class, 'destroy']);
        Route::get('list/{cate}', [DeviceController::class, 'list_by_cate']);
        Route::post('backup',[DeviceController::class,'backup_data_sensor']);
    });
});

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::get('setting/data', [SettingController::class, 'get_sensor']);
