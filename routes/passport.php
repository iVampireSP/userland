<?php

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Http\Controllers\ScopeController;
use Laravel\Passport\Http\Controllers\TransientTokenController;

Route::post('/token', [AccessTokenController::class, 'issueToken'])->middleware('throttle')->name('token');

Route::get('/authorize', [AuthorizationController::class, 'authorize'])->middleware('web', 'passport.custom_login')->name('authorizations.authorize');

$guard = config('passport.guard', null);

Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth'])->group(function () {
    Route::post('/token/refresh', [TransientTokenController::class, 'refresh'])->name('token.refresh');

    Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])->name('authorizations.approve');

    Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])->name('authorizations.deny');

    Route::get('/tokens', [AuthorizedAccessTokenController::class, 'forUser'])->name('tokens.index');

    Route::delete('/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy'])->name('tokens.destroy');

    Route::get('/clients', [ClientController::class, 'forUser'])->name('clients.index');

    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

    Route::put('/clients/{client_id}', [ClientController::class, 'update'])->name('clients.update');

    Route::delete('/clients/{client_id}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::get('/scopes', [ScopeController::class, 'all'])->name('scopes.index');

    Route::get('/personal-access-tokens', [PersonalAccessTokenController::class, 'forUser'])->name('personal.tokens.index');

    Route::post('/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])->name('personal.tokens.store');

    Route::delete('/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy'])->name('personal.tokens.destroy');
});
