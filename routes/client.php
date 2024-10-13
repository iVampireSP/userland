<?php

use App\Http\Controllers\Client\QueryController;
use Illuminate\Support\Facades\Route;

Route::get('user/{user}/bans', [QueryController::class, 'bans']);
Route::post('user/{user}/ban', [QueryController::class, 'ban']);
Route::delete('user/{user}/ban/{ban}', [QueryController::class, 'unban']);
Route::post('emailBan', [QueryController::class, 'emailBan']);
Route::get('emailBans', [QueryController::class, 'emailBans']);
Route::get('allBans', [QueryController::class, 'allBans']);
