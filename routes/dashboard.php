<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\Customer\Http\Controllers\CustomerStatusController;
use Modules\Customer\Http\Controllers\CustomerSecurityController;
use Modules\Customer\Http\Middleware\DashboardMiddleware;

Route::middleware(['auth', 'verified', DashboardMiddleware::class])
    ->prefix('dashboard')
    ->group(function () {
        // Customer CRUD
        Route::resource('customers', CustomerController::class)
            ->names('customer.customers');

        // Customer Status Actions
        Route::prefix('customers/{customer}')->name('customer.customers.')->group(function () {
            // Modal pages (GET)
            Route::get('activate', [CustomerStatusController::class, 'showActivate'])
                ->name('activate');
            Route::get('deactivate', [CustomerStatusController::class, 'showDeactivate'])
                ->name('deactivate');
            Route::get('suspend', [CustomerStatusController::class, 'showSuspend'])
                ->name('suspend');

            // Actions (PATCH)
            Route::patch('activate', [CustomerStatusController::class, 'activate'])
                ->name('activate.store');
            Route::patch('deactivate', [CustomerStatusController::class, 'deactivate'])
                ->name('deactivate.store');
            Route::patch('suspend', [CustomerStatusController::class, 'suspend'])
                ->name('suspend.store');
        });

        // Customer Security Actions
        Route::prefix('customers/{customer}')->name('customer.customers.')->group(function () {
            Route::patch('verify-email', [CustomerSecurityController::class, 'verifyEmail'])
                ->name('verify-email');
            Route::post('enable-2fa', [CustomerSecurityController::class, 'enableTwoFactor'])
                ->name('enable-2fa');
            Route::delete('disable-2fa', [CustomerSecurityController::class, 'disableTwoFactor'])
                ->name('disable-2fa');
        });
    });
