<?php

use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\BalanceController;
use App\Http\Controllers\Web\BanController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\FaceController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\PhoneController;
use App\Http\Controllers\Web\PushAppController;
use App\Http\Controllers\Web\RealNameController;
use App\Http\Controllers\Web\TokenController;
use App\Http\Controllers\Web\WeChatController;
use Illuminate\Support\Facades\Route;

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
        Route::get('clients/{client}/push-apps', [PushAppController::class, 'index'])->name('clients.push-apps.index');
        Route::patch('clients/{client}/push-apps', [PushAppController::class, 'update'])->name('clients.push-apps.update');
        Route::delete('clients/{client}/push-apps', [PushAppController::class, 'delete'])->name('clients.push-apps.delete');
        Route::resource('tokens', TokenController::class)->except(['update', 'edit']);
        Route::delete('tokens', [TokenController::class, 'destroy_all'])->name('tokens.destroy_all');
        /* End 客户端 */

        /* Start 状态 */
        Route::post('status', [AccountController::class, 'status'])->name('status.update');
        /* End 状态 */

        /* Start 推送通知订阅 */
        Route::get('push-subscription', [AccountController::class, 'showPushSubscription'])->name('push-subscription.show');
        Route::post('push-subscription', [AccountController::class, 'storePushSubscription'])->name('push-subscription.store');
        Route::delete('push-subscription', [AccountController::class, 'deletePushSubscription'])->name('push-subscription.delete');
        Route::post('push-subscription/test', [AccountController::class, 'sendTestPushNotification'])->name('push-subscription.test');
        /* End 推送通知订阅 */

        /* Start 手机号 */
        Route::get('phone', [PhoneController::class, 'create'])->name('phone.create');
        Route::post('phone', [PhoneController::class, 'store'])->name('phone.store');
        Route::post('phone/verify', [PhoneController::class, 'verify'])->name('phone.verify');
        Route::get('phone/validate', [PhoneController::class, 'show_validate_form'])->name('phone.validate');
        Route::post('phone/validate/send', [PhoneController::class, 'send_validate_code'])->middleware('throttle:3,1')->name('phone.validate.send');
        Route::post('phone/validate', [PhoneController::class, 'validate_code']);
        Route::post('phone/resend', [PhoneController::class, 'resend'])->middleware('throttle:3,1')->name('phone.resend');
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

        /* Start 权限 */
        Route::get('permissions', [PermissionController::class, 'permissions'])->name('permissions.index');
        Route::get('roles', [PermissionController::class, 'roles'])->name('roles.index');
        /* End 权限 */

        /* Start 计费 */
        Route::get('packages/list', [PackageController::class, 'list'])->name('packages.list');
        Route::resource('packages', PackageController::class)->except('edit', 'destroy');
        Route::resource('orders', OrderController::class)->only('index', 'store', 'show');
        Route::get('packages/{userPackage}/renew', [PackageController::class, 'renewPage'])->name('packages.renew');
        /* End 计费 */

        /* Start 财务 */
        Route::resource('balances', BalanceController::class)->only('index', 'store');
        Route::get('/balances/{balance:order_id}', [BalanceController::class, 'show'])->withoutMiddleware('auth')->name('balances.show');
        /* End 财务 */

        // Route::get('auth_request/{token}', [AccountController::class, 'show_authrequest'])->name('auth_request.show');
        // Route::post('auth_request', [AccountController::class, 'accept_authrequest'])->name('auth_request.accept');
    }
);
