<?php

use App\Http\Controllers\Application\AuthController;
use App\Http\Controllers\Application\QueryController;
use Illuminate\Support\Facades\Route;

Route::get('user/{user}', [QueryController::class, 'user']);
Route::post('quick/face-register', [AuthController::class, 'createFaceRegister']);
Route::get('user/{user}/bans', [QueryController::class, 'bans']);
Route::post('user/{user}/ban', [QueryController::class, 'ban']);
Route::delete('user/{user}/ban/{ban}', [QueryController::class, 'unban']);
Route::post('emailBan', [QueryController::class, 'emailBan']);
Route::get('emailBans', [QueryController::class, 'emailBans']);
Route::get('allBans', [QueryController::class, 'allBans']);
