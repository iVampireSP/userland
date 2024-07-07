<?php

use App\Http\Controllers\Public\DiscoveryController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\Auth\ConfirmPasswordController;
use App\Http\Controllers\Web\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\QuickController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Auth\VerificationController;
use App\Http\Controllers\Web\BanController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\FaceController;
use App\Http\Controllers\Web\PhoneController;
use App\Http\Controllers\Web\RealNameController;
use App\Http\Controllers\Web\TokenController;
use App\Http\Controllers\Web\WeChatController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AuthorizationController;

Route::get('/.well-known/openid-configuration', DiscoveryController::class)
    ->name('openid.discovery');

//Route::get('/.well-known/jwks', JwksController::class)
//    ->name('openid.jwks');

Route::get('/', [AccountController::class, 'index'])->middleware(['auth:web', 'banned'])->name('index');

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

    Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

    Route::get('token/{token}', [AccountController::class, 'fastLogin'])->name('auth.fast-login');

    /* Start 快速操作 */
    Route::get('quick-login/{token}', [QuickController::class, 'quickLogin'])->name('quick.login');
    /* End 快速操作 */
});

Route::middleware(['auth:web', 'banned', 'verified'])->group(
    function () {
        /* Start 账户区域 */
        Route::withoutMiddleware(['banned', 'verified'])->group(
            function () {
                Route::view('banned', 'banned')->withoutMiddleware(['banned', 'verified'])->name('banned');
            }
        );

        Route::withoutMiddleware('verified')->patch('user', [AccountController::class, 'update'])->name('users.update');
        Route::withoutMiddleware('verified')->delete('user', [AccountController::class, 'destroy'])->name('users.destroy');
        Route::view('user/delete', 'delete')->withoutMiddleware('verified')->name('users.delete');
        /* End 账户区域 */

        /* Start 电子邮件修改 */
        Route::withoutMiddleware(['verified'])->group(
            function () {
                Route::get('email/change', [AccountController::class, 'showChangeEmailForm'])->name('email.edit');
                Route::post('email/change', [AccountController::class, 'sendChangeEmail']);
                Route::get('email/change/{token}', [AccountController::class, 'changeEmail'])->name('email.change');
            }
        );
        /* End 电子邮件修改 */

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
        Route::post('status', [AccountController::class, 'status'])->name('status.update');
        /* End 状态 */

        /* Start 手机号 */
        Route::get('phone', [PhoneController::class, 'create'])->name('phone.create');
        Route::post('phone', [PhoneController::class, 'store'])->name('phone.store');
        Route::post('phone/verify', [PhoneController::class, 'verify'])->name('phone.verify');
        Route::get('phone/validate', [PhoneController::class, 'show_validate_form'])->name('phone.validate');
        Route::post('phone/validate/send', [PhoneController::class, 'send_validate_code'])->name('phone.validate.send');
        Route::post('phone/validate', [PhoneController::class, 'validate_code']);
        Route::post('phone/resend', [PhoneController::class, 'resend'])->name('phone.resend');
        Route::get('phone/edit', [PhoneController::class, 'edit'])->middleware('phone.confirm')->name('phone.edit');
        Route::delete('phone', [PhoneController::class, 'unbind'])->middleware('phone.confirm')->name('phone.unbind');
        /* End 手机号 */

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

        /* Start 微信绑定 */
        Route::get('wechat', [WeChatController::class, 'index'])->name('wechat.bind');
        Route::delete('wechat', [WeChatController::class, 'unbind'])->name('wechat.unbind');
        /* End 微信绑定 */

        // Route::get('auth_request/{token}', [AccountController::class, 'show_authrequest'])->name('auth_request.show');
        // Route::post('auth_request', [AccountController::class, 'accept_authrequest'])->name('auth_request.accept');
    }
);

Route::get('scopes', [TokenController::class, 'display_scopes'])->name('tokens.scopes');

Route::view('tos', 'tos')->name('tos');
Route::view('privacy_policy', 'privacy_policy')->name('privacy_policy');

/* Passport Route override */
Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->middleware('web', 'passport.custom_login')->name('passport.authorizations.authorize');
