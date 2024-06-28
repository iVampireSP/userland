<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('user', [UserController::class, 'user'])->name('openid.userinfo');

// 获取，创建或更新状态
Route::match(['get', 'post'], 'status', [UserController::class, 'status'])
    ->middleware('scopes:profile');

// Route::post('login', [UserController::class, 'fastLogin'])->middleware('scopes:login');
