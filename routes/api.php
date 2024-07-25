<?php

use App\Http\Controllers\Api\AppsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
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

Route::middleware(['auth.jwt'])->group(function () {
    Route::get('v1/syncUserSimpeg', [UserController::class, 'syncUserSimpeg']);
    // Route::get('v1/auth/apps/kategori', [UserController::class, 'authAppsKategori']);

    Route::get('v1/me', [AuthController::class, 'me']);

    Route::post('v1/user/unlock', [UserController::class, 'authApps']);
    // Route::get('v1/user/apps', [UserController::class, 'authApps']);
    // Route::get('v1/user/apps/filtur', [UserController::class, 'authApps']);
    // Route::get('v1/user/apps/role', [UserController::class, 'authApps']);

    // Route::get('v1/apps', [UserController::class, 'authApps']);
    // Route::get('v1/apps/filtur', [UserController::class, 'authAppsFiltur']);
    // Route::get('v1/apps/Role', [UserController::class, 'authAppsRole']);

    Route::post('v1/logout/{token}', [AuthController::class, 'logout']);
    Route::post('v1/logoutDevice/{userTokenId}', [AuthController::class, 'logoutDevice']);
    Route::post('v1/logoutAllDevice', [AuthController::class, 'logoutAllDevice']);
});
Route::get('v1/public/apps', [AppsController::class, 'get']);

// Route::post('v1/daftar', [AuthController::class, 'daftar']);
Route::post('v1/login', [AuthController::class, 'login']);
Route::post('v1/token', [AuthController::class, 'tokenRefresh']);
// Route::get('v1/apps', [UserController::class, 'apps']);
