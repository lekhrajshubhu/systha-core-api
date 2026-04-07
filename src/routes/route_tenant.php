<?php

use Illuminate\Support\Facades\Route;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Appointment\AppointmentController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Appointment\AppointmentPaymentController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Auth\AuthVendorClientApiController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\EmailLog\EmailLogController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Inquiry\InquiryController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Invoice\InvoiceController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Message\MessageController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Payment\PaymentController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\PaymentMethod\PaymentMethodController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Quotation\QuotationController;
use Systha\Core\Http\Controllers\Api\V1\Tenant\Subscription\SubscriptionController;

// Vendor client auth
Route::group([
    'prefix' => 'api/v1/tenant',
    'middleware' => ['api', 'vendor.client.domain'],
], function () {
    /**
     * Login
     * @group Tenant
     * @subgroup Auth
     */
    Route::post('login', [AuthVendorClientApiController::class, 'login'])->name('tenant.login');

    /**
     * Reset Password
     * @group Tenant
     * @subgroup Auth
     */
    Route::post('password-reset', [AuthVendorClientApiController::class, 'resetPassword'])->name('tenant.password.reset');

    Route::group(['middleware' => ['auth:vendor_client']], function () {
        /**
         * Profile
         * @group Tenant
         * @subgroup Profile
         */
        Route::get('profile', [AuthVendorClientApiController::class, 'profile'])->name('tenant.profile');

        /**
         * Update Profile
         * @group Tenant
         * @subgroup Profile
         */
        Route::put('profile', [AuthVendorClientApiController::class, 'updateProfile'])->name('tenant.update.profile');

        /**
         * Update Profile
         * @group Tenant
         * @subgroup Profile
         */
        Route::patch('profile', [AuthVendorClientApiController::class, 'updateProfile'])->name('tenant.update.profile.2');

        /**
         * Update Profile Address
         * @group Tenant
         * @subgroup Profile
         */
        Route::put('profile-address', [AuthVendorClientApiController::class, 'updateProfileAddress'])->name('tenant.update.profile.address');

        /**
         * Update Profile Address
         * @group Tenant
         * @subgroup Profile
         */
        Route::patch('profile-address', [AuthVendorClientApiController::class, 'updateProfileAddress'])->name('tenant.update.profile.address.2');

        /**
         * Update Profile Password
         * @group Tenant
         * @subgroup Profile
         */
        Route::put('profile-update-password', [AuthVendorClientApiController::class, 'updateProfilePassword'])->name('tenant.update.profile.password');

        /**
         * Update Profile Password
         * @group Tenant
         * @subgroup Profile
         */
        Route::patch('profile-update-password', [AuthVendorClientApiController::class, 'updateProfilePassword'])->name('tenant.update.profile.password.2');

        /**
         * Logout
         * @group Tenant
         * @subgroup Auth
         */
        Route::post('logout', [AuthVendorClientApiController::class, 'logout'])->name('tenant.logout');
    });

    Route::group([
        'middleware' => ['auth:vendor_client']
    ], function () {
        /**
         * Index
         * @group Tenant
         * @subgroup Inquiries
         */
        Route::get('inquiries', [InquiryController::class, 'index'])->name('tenant.inquiries');

        /**
         * Show
         * @group Tenant
         * @subgroup Inquiries
         */
        Route::get('inquiries/{id}', [InquiryController::class, 'show'])->name('tenant.inquiries.by.id');

        /**
         * Index
         * @group Tenant
         * @subgroup Quotations
         */
        // Route::get('quotations', [QuotationController::class, 'index'])->name('tenant.quotations');

        /**
         * Show
         * @group Tenant
         * @subgroup Quotations
         */
        Route::get('quotations/{id}', [QuotationController::class, 'show'])->name('tenant.quotations.by.id');

        /**
         * Confirm Quotation
         * @group Tenant
         * @subgroup Quotations
         */
        Route::post('quotations/{id}/confirm', [QuotationController::class, 'confirmQuotation'])->name('tenant.quotations.confirm.by.id');

        /**
         * Add Payment Method
         * @group Tenant
         * @subgroup Payments
         */
        Route::post('payment-methods/create', [PaymentController::class, 'addPaymentMethod'])->name('tenant.payment.methods.create');

        /**
         * Make Default Payment Method
         * @group Tenant
         * @subgroup Payments
         */
        Route::post('payment-methods/{id}/make-default', [PaymentController::class, 'makeDefaultPaymentMethod'])->name('tenant.payment.methods.make.default.by.id');

        /**
         * Update Payment Method
         * @group Tenant
         * @subgroup Payments
         */
        Route::post('payment-methods/{id}/update', [PaymentController::class, 'updatePaymentMethod'])->name('tenant.payment.methods.update.by.id');

        /**
         * Delete Payment Method
         * @group Tenant
         * @subgroup Payments
         */
        Route::post('payment-methods/{id}/delete', [PaymentController::class, 'deletePaymentMethod'])->name('tenant.payment.methods.delete.by.id');

        /**
         * Conversations
         * @group Tenant
         * @subgroup Messages
         */
        Route::get('conversations', [MessageController::class, 'conversations'])->name('tenant.conversations');

        /**
         * Show
         * @group Tenant
         * @subgroup Messages
         */
        Route::get('conversations/{id}', [MessageController::class, 'show'])->name('tenant.conversations.by.id');

        /**
         * Send Message
         * @group Tenant
         * @subgroup Messages
         */
        Route::post('conversations/{id}/send-message', [MessageController::class, 'sendMessage'])->name('tenant.conversations.send.message.by.id');
    });

    Route::group([
        'prefix' => 'appointments',
        'middleware' => ['auth:vendor_client']
    ], function () {
        /**
         * Index
         * @group Tenant
         * @subgroup Appointments
         */
        Route::get('', [AppointmentController::class, 'index'])->name('tenant.appointments');

        /**
         * Show
         * @group Tenant
         * @subgroup Appointments
         */
        Route::get('/{id}', [AppointmentController::class, 'show'])->name('tenant.appointments.by.id');

        /**
         * Show
         * @group Tenant
         * @subgroup Appointments
         */
        Route::get('/{id}', [AppointmentController::class, 'show'])->name('tenant.appointments.by.id.2');

        /**
         * Store Card Payment
         * @group Tenant
         * @subgroup Appointments
         */
        Route::post('/{id}/store-card-payment', [AppointmentPaymentController::class, 'storeCardPayment'])->name('tenant.appointments.store.card.payment.by.id');

        /**
         * Create Payment Intent
         * @group Tenant
         * @subgroup Appointments
         */
        Route::post('/{id}/payment-intent', [AppointmentPaymentController::class, 'createPaymentIntent'])->name('tenant.appointments.payment.intent.by.id');

        /**
         * Cash Payment
         * @group Tenant
         * @subgroup Appointments
         */
        // Route::post('/{id}/cash-payment', [AppointmentPaymentController::class, 'cashPayment'])->name('tenant.appointments.cash.payment.by.id');
    });

    Route::group([
        'prefix' => 'subscriptions',
        'middleware' => ['auth:vendor_client']
    ], function () {
        /**
         * Index
         * @group Tenant
         * @subgroup Payments
         */
        Route::get('', [SubscriptionController::class, 'index'])->name('tenant.subscriptions');

        /**
         * Show
         * @group Tenant
         * @subgroup Payments
         */
        Route::get('/{id}', [SubscriptionController::class, 'show'])->name('tenant.subscriptions.by.id');

        /**
         * Cancel
         * @group Tenant
         * @subgroup Payments
         */
        Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('tenant.subscriptions.cancel.by.id');
    });

    Route::group([
        'prefix' => 'invoices',
        'middleware' => ['auth:vendor_client']
    ], function () {
        /**
         * Index
         * @group Tenant
         * @subgroup Invoices
         */
        Route::get('', [InvoiceController::class, 'index'])->name('tenant.invoices');        // Route::get('conversations/{id}', [MessageController::class, 'show']);
        // Route::post('conversations/{id}/send-message', [MessageController::class, 'sendMessage']);

    });

    Route::group([
        'prefix' => 'payments',
        'middleware' => ['auth:vendor_client']
    ], function () {
        /**
         * Index
         * @group Tenant
         * @subgroup Payments
         */
        Route::get('', [PaymentController::class, 'index'])->name('tenant.payments');
    });

    Route::group([
        'prefix' => 'email-logs',
        'middleware' => ['auth:vendor_client']
    ], function () {
        /**
         * Index
         * @group Tenant
         * @subgroup Messages
         */
        Route::get('', [EmailLogController::class, 'index'])->name('tenant.email.logs');
    });

    Route::group([
        'prefix' => 'payment-methods',
        'middleware' => ['auth:vendor_client']
    ], function () {
        /**
         * Index
         * @group Tenant
         * @subgroup Payments
         */
        Route::get('', [PaymentMethodController::class, 'index'])->name('tenant.payment.methods');
    });
});
