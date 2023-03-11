<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\HostController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\NodeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReplyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\Admin\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->name('index')->middleware('auth:admin')->withoutMiddleware('admin.validateReferer');

Route::resource('admins', AdminController::class)->except('show');

Route::resource('users', UserController::class);

Route::resource('clients', ClientController::class);

Route::resource('notifications', NotificationController::class)->only(['create', 'store']);

Route::withoutMiddleware(['auth:admin', 'admin.validateReferer'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
