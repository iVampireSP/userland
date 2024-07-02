<?php

use App\Http\Controllers\Public\DiscoveryController;
use App\Http\Controllers\Public\JwksController;
use App\Http\Controllers\Web\Auth\ConfirmPasswordController;
use App\Http\Controllers\Web\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Auth\VerificationController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BanController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\FaceController;
use App\Http\Controllers\Web\RealNameController;
use App\Http\Controllers\Web\TokenController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AuthorizationController;

Route::get('/.well-known/openid-configuration', DiscoveryController::class)
    ->name('openid.discovery');

//Route::get('/.well-known/jwks', JwksController::class)
//    ->name('openid.jwks');

Route::get('/', [AuthController::class, 'index'])->middleware('banned')->name('index');

Route::prefix('auth')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('login/{client}', [LoginController::class, 'showCustomLoginForm'])->name('login.custom');
    Route::post('login', [LoginController::class, 'passwordLogin']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('face-login', [LoginController::class, 'showFaceLoginForm'])->name('login.face-login');
    Route::post('face-login', [LoginController::class, 'faceLogin']);
    Route::get('select', [LoginController::class, 'selectAccount'])->name('login.select');
    Route::post('switch', [LoginController::class, 'switchAccount'])->name('login.switch');

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

    Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

    Route::get('token/{token}', [AuthController::class, 'fastLogin'])->name('auth.fast-login');
});

Route::middleware(['auth:web', 'banned', 'verified'])->group(
    function () {
        /* Start 账户区域 */
        Route::withoutMiddleware(['banned', 'verified'])->group(
            function () {
                Route::view('banned', 'banned')->withoutMiddleware(['banned', 'verified'])->name('banned');
            }
        );

        Route::withoutMiddleware('verified')->patch('user', [AuthController::class, 'update'])->name('users.update');
        Route::withoutMiddleware('verified')->delete('user', [AuthController::class, 'destroy'])->name('users.destroy');
        Route::view('user/delete', 'delete')->withoutMiddleware('verified')->name('users.delete');
        /* End 账户区域 */

        /* Start 实名认证 */
        Route::get('real_name', [RealNameController::class, 'create'])->name('real_name.create');
        Route::post('real_name', [RealNameController::class, 'store'])->name('real_name.store');
        Route::match(['get', 'post'], 'real_name/pay', [RealNameController::class, 'pay'])->name('real_name.pay');
        Route::get('real_name/capture', [RealNameController::class, 'capture'])->name('real_name.capture');
        Route::post('real_name/capture', [RealNameController::class, 'capture']);
        /* End 实名证 */

        /* Start 客户端 */
        Route::resource('clients', ClientController::class);
        Route::resource('tokens', TokenController::class)->except(['update', 'edit']);
        Route::delete('tokens', [TokenController::class, 'destroy_all'])->name('tokens.destroy_all');
        /* End 客户端 */

        /* Start 状态 */
        Route::post('status', [AuthController::class, 'status'])->name('status.update');
        /* End 状态 */

        /* Start 封禁 */
        Route::resource('bans', BanController::class)->only(['index']);
        /* End 封禁 */

        /* Start 人脸 */
        Route::get('faces', [FaceController::class, 'index'])->name('faces.index');
        Route::get('faces/capture', [FaceController::class, 'capture'])->name('faces.capture');
        Route::post('faces/capture', [FaceController::class, 'capture']);
        Route::delete('faces', [FaceController::class, 'destroy'])->name('faces.destroy');

        Route::get('faces/test', [FaceController::class, 'test'])->name('faces.test');
        Route::post('faces/test', [FaceController::class, 'test']);
        /* End 人脸 */

        // Route::get('auth_request/{token}', [AuthController::class, 'show_authrequest'])->name('auth_request.show');
        // Route::post('auth_request', [AuthController::class, 'accept_authrequest'])->name('auth_request.accept');
    }
);

Route::get('scopes', [TokenController::class, 'display_scopes'])->name('tokens.scopes');

Route::view('tos', 'tos')->name('tos');
Route::view('privacy_policy', 'privacy_policy')->name('privacy_policy');

/* Passport Route override */
Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->middleware('web', 'passport.custom_login')->name('passport.authorizations.authorize');
