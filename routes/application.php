<?php

use App\Http\Controllers\Application\AuthController;
use App\Http\Controllers\Application\BalanceController;
use Illuminate\Support\Facades\Route;

Route::get('user/{user}', [AuthController::class, 'user']);
Route::post('quick/face-register', [AuthController::class, 'createFaceRegister']);

Route::post('balances/reduce', [BalanceController::class, 'reduce']);
Route::post('balances/add', [BalanceController::class, 'add']);
Route::post('balances/unit_reduce', [BalanceController::class, 'unit_reduce']);
Route::post('balances/unit_add', [BalanceController::class, 'unit_add']);
Route::post('balances/can_bill_unit', [BalanceController::class, 'can_bill_unit']);
Route::post('balances/balance_enough', [BalanceController::class, 'balance_enough']);
