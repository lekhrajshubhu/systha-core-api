<?php

use Illuminate\Support\Facades\Route;
use Systha\Core\Http\Controllers\CoreController;
use Systha\Core\Http\Controllers\File\FileController;
use Systha\Core\Http\Controllers\StripeWebhook\StripeWebhookController;

Route::view('/vendor-clients/{any}', 'core::customer.index')->where('any', '.*')->name('core.vendor.clients.by.any');

Route::view('/users', 'core::customer.index')->name('core.users');

Route::view('/global-clients/{any}', 'core::customer.index')->where('any', '.*')->name('core.global.clients.by.any');

Route::view('/accounts', 'core::customer.index')->name('core.accounts');

/**
 * @group Core
 * @subgroup Profile
 */
Route::get('media/{filename}', [FileController::class, 'showImage'])->name('media.show');

Route::group([
    'prefix' => 'api/v1',
    'middleware' => ['api'],
], function () {
    // Public routes

    /**
     * @group Core
     * @subgroup Profile
     */
    Route::get('logo/{file_name}', [CoreController::class, 'vendorLogo'])->name('core.vendor.logo.core');

    /**
     * @group Core
     * @subgroup Profile
     */
    Route::get('avatar/{file_name}', [CoreController::class, 'avatar'])->name('core.avatar.core');
});

/**
 * @group Core
 * @subgroup Profile
 */
Route::post('/stripe-webhook', [StripeWebhookController::class, 'handle'])->name('core.handle.stripe.webhook');
