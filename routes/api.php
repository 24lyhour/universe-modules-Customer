<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\Api\V1\Customer\CustomerApiController;
use Modules\Customer\Http\Controllers\Api\V1\Customer\CustomerAuthController;
use Modules\Customer\Http\Controllers\Api\V1\Customer\CustomerRegisterController;
use Modules\Customer\Http\Controllers\Api\V1\Customer\CustomerStatusApiController;
use Modules\Customer\Http\Controllers\Api\V1\Customer\DeviceTokenController;

// Public auth routes (no authentication required)
Route::prefix('v1/auth')->group(function () {
    // Login
    Route::post('login', [CustomerAuthController::class, 'login'])->name('customer.auth.login');

    // Registration
    Route::post('register', [CustomerRegisterController::class, 'register'])->name('customer.auth.register');
    Route::post('check-email', [CustomerRegisterController::class, 'checkEmail'])->name('customer.auth.check-email');
    Route::post('check-phone', [CustomerRegisterController::class, 'checkPhone'])->name('customer.auth.check-phone');

    // OTP
    Route::post('otp/send', [CustomerRegisterController::class, 'sendOtp'])->name('customer.auth.otp.send');
    Route::post('otp/verify', [CustomerRegisterController::class, 'verifyOtp'])->name('customer.auth.otp.verify');
});

// Protected auth routes (requires authentication)
Route::middleware(['auth:sanctum'])->prefix('v1/auth')->group(function () {
    Route::post('logout', [CustomerAuthController::class, 'logout'])->name('customer.auth.logout');
    Route::post('logout-all', [CustomerAuthController::class, 'logoutAll'])->name('customer.auth.logout-all');
    Route::get('customer', [CustomerAuthController::class, 'me'])->name('customer.auth.customer');
    Route::post('customer', [CustomerAuthController::class, 'update'])->name('customer.auth.customer.update');

    // Device Token (FCM)
    Route::post('device-token', [DeviceTokenController::class, 'store'])->name('customer.auth.device-token.store');
    Route::delete('device-token', [DeviceTokenController::class, 'destroy'])->name('customer.auth.device-token.destroy');
});

// Protected customer routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Customer CRUD
    Route::apiResource('customers', CustomerApiController::class)
        ->names('customer');

    // Customer Stats & Search
    Route::get('customers-stats', [CustomerApiController::class, 'stats'])
        ->name('customer.stats');
    Route::get('customers-search', [CustomerApiController::class, 'search'])
        ->name('customer.search');

    // Customer Status Actions
    Route::prefix('customers/{customer}')->name('customer.')->group(function () {
        Route::patch('activate', [CustomerStatusApiController::class, 'activate'])
            ->name('activate');
        Route::patch('deactivate', [CustomerStatusApiController::class, 'deactivate'])
            ->name('deactivate');
        Route::patch('suspend', [CustomerStatusApiController::class, 'suspend'])
            ->name('suspend');
    });
});
