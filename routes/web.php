<?php

use App\Http\Controllers\Public\DiscoveryController;
use App\Http\Controllers\Public\JwksController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\FaceVerificationController;
use App\Http\Controllers\Web\TokenController;
use App\Http\Controllers\Web\UnitPriceController;
use Illuminate\Support\Facades\Route;

Route::get('/.well-known/openid-configuration', DiscoveryController::class)
    ->name('openid.discovery');

Route::get('/.well-known/jwks', JwksController::class)
    ->name('openid.jwks');

Route::get('/', [AccountController::class, 'index'])->middleware(['auth:web', 'banned'])->name('index');

Route::get('scopes', [TokenController::class, 'display_scopes'])->name('tokens.scopes');
Route::get('unit_prices', [UnitPriceController::class, 'index'])->name('units.price');

Route::view('tos', 'tos')->name('tos');
Route::view('privacy_policy', 'privacy_policy')->name('privacy_policy');

Route::prefix('face/verification')->group(function () {
    Route::post('start', [FaceVerificationController::class, 'startVerification']);
    Route::post('initial', [FaceVerificationController::class, 'submitInitialFace']);
    Route::post('action', [FaceVerificationController::class, 'submitActionVerification']);
    Route::post('flash', [FaceVerificationController::class, 'submitFlashVerification']);
    Route::post('next-flash', [FaceVerificationController::class, 'getNextFlashColor']);
    Route::post('verify-flash', [FaceVerificationController::class, 'verifyFlash']);
});
