<?php

use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\Auth\ConfirmPasswordController;
use App\Http\Controllers\Web\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\QuickController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Auth\VerificationController;

Route::prefix('auth')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('login/{client}', [LoginController::class, 'showCustomLoginForm'])->name('login.custom');
    Route::middleware(['recaptcha', 'throttle:10,1'])->post('login', [LoginController::class, 'passwordLogin']);

    /* Start 短信验证码登录 */
    Route::middleware(['throttle:3,1'])->post('sms-login', [LoginController::class, 'sendSMS'])->name('login.sms');
    Route::middleware(['throttle:10,1'])->post('sms-login/validate', [LoginController::class, 'SMSValidate'])->name('login.sms.validate');
    /* End 短信验证码登录 */

    /* Start 口令登录 */
    Route::middleware(['recaptcha', 'throttle:5,1'])->post('token-login', [LoginController::class, 'tokenLogin'])->name('login.token');
    /* End 口令登录 */

    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('logout-all', [LoginController::class, 'logoutAll'])->name('logout.all');
    Route::get('face-login', [LoginController::class, 'showFaceLoginForm'])->name('login.face-login');
    Route::middleware(['recaptcha'])->post('face-login', [LoginController::class, 'faceLogin']);
    Route::get('select', [AccountController::class, 'selectAccount'])->name('login.select');
    Route::post('switch', [AccountController::class, 'switchAccount'])->name('login.switch');

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::middleware(['recaptcha', 'throttle:5,1'])->post('register', [RegisterController::class, 'register']);

    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

    Route::withoutMiddleware(['banned', 'verified'])->group(function () {
        Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
        Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
    });

    Route::get('token/{token}', [AccountController::class, 'fastLogin'])->name('auth.fast-login');

    /* Start 快速操作 */
    Route::get('quick-login/{token}', [QuickController::class, 'quickLogin'])->name('quick.login');
    /* End 快速操作 */
});

/* Passport Route override */
Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->middleware('web', 'passport.custom_login')->name('passport.authorizations.authorize');
