<?php

use Illuminate\Support\Facades\Route;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Auth\AuthContactApiController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Inquiry\InquiryController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Quotation\QuotationController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Payment\PaymentController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Message\MessageController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Appointment\AppointmentController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Appointment\AppointmentPaymentController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Subscription\SubscriptionController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\Invoice\InvoiceController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\EmailLog\EmailLogController;
use Systha\Core\Http\Controllers\Api\V1\ContactClient\PaymentMethod\PaymentMethodController;

// // Contact auth
// Route::group([
// 	'prefix' => 'api/v1/contacts',
// 	'middleware' => ['api'],
// ], function () {
// 	/**
// 	 * @group Contacts
// 	 * @subgroup Auth
// 	 */
// 	Route::post('login', [AuthContactApiController::class, 'login'])->name('contact.login');

// 	Route::group(
// 		[
// 			'middleware' => ['auth:contacts']
// 		],
// 		function () {

// 			/**
// 			 * @group Contacts
// 			 * @subgroup Profile
// 			 */
// 			Route::get('profile', [AuthContactApiController::class, 'profile'])->name('contact.profile.contact');

// 			/**
// 			 * @group Contacts
// 			 * @subgroup Auth
// 			 */
// 			Route::post('logout', [AuthContactApiController::class, 'logout'])->name('contact.logout');
// 		}
// 	);

// 	Route::group([
// 		'middleware' => ['auth:contacts']
// 	], function () {
// 		/**
// 		 * @group Contacts
// 		 * @subgroup Inquiries
// 		 */
// 		Route::get('inquiries', [InquiryController::class, 'index'])->name('contact.index');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Inquiries
// 		 */
// 		Route::get('inquiries/{id}', [InquiryController::class, 'show'])->name('contact.show');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Quotations
// 		 */
// 		Route::get('quotations', [QuotationController::class, 'index'])->name('contact.index.2');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Quotations
// 		 */
// 		Route::get('quotations/{id}', [QuotationController::class, 'show'])->name('contact.show.2');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Quotations
// 		 */
// 		Route::post('quotations/{id}/confirm', [QuotationController::class, 'confirmQuotation'])->name('contact.confirm.quotation.quotation');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Payments
// 		 */
// 		Route::post('add-payment-method', [PaymentController::class, 'addPaymentMethod'])->name('contact.add.payment.method.payment');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Payments
// 		 */
// 		Route::post('payment-methods/{id}/make-default', [PaymentController::class, 'makeDefaultPaymentMethod'])->name('contact.make.default.payment.method.payment');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Messages
// 		 */
// 		Route::get('conversations', [MessageController::class, 'conversations'])->name('contact.conversations.message');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Messages
// 		 */
// 		Route::get('conversations/{id}', [MessageController::class, 'show'])->name('contact.show.3');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Messages
// 		 */
// 		Route::post('conversations/{id}/send-message', [MessageController::class, 'sendMessage'])->name('contact.send.message.message');
// 	});

// 	Route::group([
// 		'prefix' => 'appointments',
// 		'middleware' => ['auth:contacts']
// 	], function () {
// 		/**
// 		 * @group Contacts
// 		 * @subgroup Appointments
// 		 */
// 		Route::get('', [AppointmentController::class, 'index'])->name('contact.index.3');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Appointments
// 		 */
// 		Route::get('/{id}', [AppointmentController::class, 'show'])->name('contact.show.4');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Appointments
// 		 */
// 		Route::get('/{id}', [AppointmentController::class, 'show'])->name('contact.show.5');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Appointments
// 		 */
// 		Route::post('/{id}/store-card-payment', [AppointmentPaymentController::class, 'storeCardPayment'])->name('contact.store.card.payment.appointment.payment');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Appointments
// 		 */
// 		Route::post('/{id}/payment-intent', [AppointmentPaymentController::class, 'createPaymentIntent'])->name('contact.create.payment.intent.appointment.payment');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Appointments
// 		 */
// 		Route::post('/{id}/cash-payment', [AppointmentPaymentController::class, 'cashPayment'])->name('contact.cash.payment.appointment.payment');
// 	});

// 	Route::group([
// 		'prefix' => 'subscriptions',
// 		'middleware' => ['auth:contacts']
// 	], function () {
// 		/**
// 		 * @group Contacts
// 		 * @subgroup Payments
// 		 */
// 		Route::get('', [SubscriptionController::class, 'index'])->name('contact.index.4');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Payments
// 		 */
// 		Route::get('/{id}', [SubscriptionController::class, 'show'])->name('contact.show.6');

// 		/**
// 		 * @group Contacts
// 		 * @subgroup Payments
// 		 */
// 		Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('contact.cancel.subscription');
// 	});

// 	Route::group([
// 		'prefix' => 'invoices',
// 		'middleware' => ['auth:contacts']
// 	], function () {
// 		/**
// 		 * @group Contacts
// 		 * @subgroup Invoices
// 		 */
// 		Route::get('', [InvoiceController::class, 'index'])->name('contact.index.5');        // Route::get('conversations/{id}', [MessageController::class, 'show']);
// 		// Route::post('conversations/{id}/send-message', [MessageController::class, 'sendMessage']);

// 	});

// 	Route::group([
// 		'prefix' => 'payments',
// 		'middleware' => ['auth:contacts']
// 	], function () {
// 		/**
// 		 * @group Contacts
// 		 * @subgroup Payments
// 		 */
// 		Route::get('', [PaymentController::class, 'index'])->name('contact.index.6');
// 	});

// 	Route::group([
// 		'prefix' => 'email-logs',
// 		'middleware' => ['auth:contacts']
// 	], function () {
// 		/**
// 		 * @group Contacts
// 		 * @subgroup Messages
// 		 */
// 		Route::get('', [EmailLogController::class, 'index'])->name('contact.index.7');
// 	});

// 	Route::group([
// 		'prefix' => 'payment-methods',
// 		'middleware' => ['auth:contacts']
// 	], function () {
// 		/**
// 		 * @group Contacts
// 		 * @subgroup Payments
// 		 */
// 		Route::get('', [PaymentMethodController::class, 'index'])->name('contact.index.8');
// 	});
// });
