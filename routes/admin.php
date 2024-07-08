<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\NotificationController;
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

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
