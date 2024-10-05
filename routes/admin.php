<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth:web', 'auth:admin',  'admin.validateReferer'])->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::middleware(['throttle:10,1'])->post('/', [AuthController::class, 'login']);
});

Route::get('/home', [AuthController::class, 'index'])->name('index');

Route::resource('admins', AdminController::class)->except('show');

Route::resource('users', UserController::class);

Route::resource('user/{user}/bans', BanController::class);

Route::resource('clients', ClientController::class);

Route::resource('notifications', NotificationController::class)->only(['create', 'store']);

Route::resource('features', FeatureController::class);
Route::post('/features/{feature}/restore', [FeatureController::class, 'restore'])->name('features.restore');

Route::resource('plans', PlanController::class);
Route::post('/plans/{plan}/restore', [PlanController::class, 'restore'])->name('plans.restore');
Route::get('/plans/{plan}/features', [PlanController::class, 'features'])->name('plans.features');
Route::post('/plans/{plan}/features/{feature}', [PlanController::class, 'toggleFeature'])->name('plans.toggleFeature');

Route::resource('roles', RoleController::class);
Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
Route::post('/roles/{role}/permissions/{permission}', [RoleController::class, 'togglePermission'])->name('roles.permissions.toggle');

Route::resource('permissions', PermissionController::class);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
