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

Route::get('user', [UserController::class, 'user'])->middleware('scopes:user');

Route::get('real-name', [UserController::class, 'realName'])
    ->middleware('scopes:realname');

Route::post('login', [UserController::class, 'fastLogin'])->middleware('scopes:login');
