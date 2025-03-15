<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PackageCategoryController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PackagePermissionController;
use App\Http\Controllers\Admin\PackageUpgradeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PushAppController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UnitPriceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPermissionController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth:web', 'auth:admin', 'admin.validateReferer'])->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::middleware(['throttle:10,1'])->post('/', [AuthController::class, 'login']);
});

Route::get('/home', [AuthController::class, 'index'])->name('index');

Route::resource('admins', AdminController::class)->except('show');

Route::resource('users', UserController::class);

Route::resource('user/{user}/bans', BanController::class);
Route::get('/users/{user}/roles', [UserPermissionController::class, 'roles'])->name('users.roles');
Route::post('/users/{user}/roles/{role}', [UserPermissionController::class, 'toggleRole'])->name('users.roles.toggle');
Route::get('/users/{user}/permissions', [UserPermissionController::class, 'permissions'])->name('users.permissions');
Route::post('/users/{user}/permissions/{permission}', [UserPermissionController::class, 'togglePermission'])->name('users.permissions.toggle');

Route::resource('clients', ClientController::class);
// Route::post('clients/{client}/tenant', [ClientController::class, 'enableTenant'])->name('clients.tenant.enable');
// Route::delete('/clients/{client}/tenant', [ClientController::class, 'disableTenant'])->name('clients.tenant.disable');

Route::resource('notifications', NotificationController::class)->only(['create', 'store']);

Route::resource('roles', RoleController::class);
Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
Route::post('/roles/{role}/permissions/{permission}', [RoleController::class, 'togglePermission'])->name('roles.permissions.toggle');

Route::resource('permissions', PermissionController::class);
// Route::resource('quotas', QuotaController::class);
Route::resource('unit_prices', UnitPriceController::class);
Route::resource('package_categories', PackageCategoryController::class);
Route::resource('packages', PackageController::class);
// Route::resource('packages.quotas', PackageQuotaController::class)->only(['index', 'update', 'destroy']);
Route::resource('packages.upgrades', PackageUpgradeController::class);

Route::get('/packages/{package}/roles', [PackagePermissionController::class, 'roles'])->name('packages.roles');
Route::post('/packages/{package}/roles/{role}', [PackagePermissionController::class, 'toggleRole'])->name('packages.roles.toggle');
Route::get('/packages/{package}/permissions', [PackagePermissionController::class, 'permissions'])->name('packages.permissions');
Route::post('/packages/{package}/permissions/{permission}', [PackagePermissionController::class, 'togglePermission'])->name('packages.permissions.toggle');

Route::resource('applications', ApplicationController::class);

// 应用管理路由
Route::get('/push_apps', [PushAppController::class, 'index'])->name('push_apps.index');
Route::get('/push_apps/create', [PushAppController::class, 'create'])->name('push_apps.create');
Route::post('/push_apps', [PushAppController::class, 'store'])->name('push_apps.store');
Route::get('/push_apps/{app}', [PushAppController::class, 'show'])->name('push_apps.show');
Route::get('/push_apps/{app}/edit', [PushAppController::class, 'edit'])->name('push_apps.edit');
Route::put('/push_apps/{app}', [PushAppController::class, 'update'])->name('push_apps.update');
Route::delete('/push_apps/{app}', [PushAppController::class, 'destroy'])->name('push_apps.destroy');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
