<?php

/**
 * 公共路由，不需要登录。这里存放 异步回调 请求路由。
 */

use App\Http\Controllers\Public\RealNameController;
use App\Http\Controllers\Public\StatusController;
use Illuminate\Support\Facades\Route;

Route::match(['post', 'get'], 'real_name/notify', [RealNameController::class, 'verify'])->name('real-name.notify');
Route::match(['post', 'get'], 'real_name/process', [RealNameController::class, 'process'])->name('real-name.process')->withoutMiddleware('csrf');

Route::match(['post', 'get'], 'real_name/pay_process', [RealNameController::class, 'payNotify'])->name('real-name.pay-notify');

Route::get('status/{user}', StatusController::class)->name('status.show');
