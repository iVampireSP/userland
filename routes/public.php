<?php

/**
 * 公共路由，不需要登录。这里存放 异步回调 请求路由。
 */

use App\Http\Controllers\Public\RealNameController;
use App\Http\Controllers\Public\StatusController;
use App\Http\Controllers\Public\WeChatController;
use App\Http\Middleware\JsonRequest;
use Illuminate\Support\Facades\Route;


Route::match(['post', 'get'], 'real_name/pay_process', [RealNameController::class, 'payNotify'])->name('real-name.pay-notify');

Route::get('status/{user}', StatusController::class)->name('status.show');

Route::withoutMiddleware(JsonRequest::class)->any('/wechat/callback', [WeChatController::class, 'serve'])->name('wechat.callback');

// /* Start Auth Request */
// Route::post('auth_request', [JWTController::class, 'store'])->name('auth_request.store');
// Route::get('auth_request/{token}', [JWTController::class, 'show'])->name('auth_request.show');
// Route::post('auth_request/refresh', [JWTController::class, 'refresh'])->name('auth_request.refresh');
// /* End Auth Request */
