<?php

use Illuminate\Support\Facades\Route;
use Systha\Core\Http\Controllers\Api\V1\Platform\AppInit\AppInitializationController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Appointment\AppointmentController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Appointment\AppointmentPaymentController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Auth\AuthLoginController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Auth\AuthProfileController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Auth\PasswordResetController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Auth\SignupController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Dashboard\DashboardController;
use Systha\Core\Http\Controllers\Api\V1\Platform\EmailLog\EmailLogController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Inquiry\InquiryController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Inquiry\InquiryStoreController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Inspection\InspectionController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Invoice\InvoiceController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Message\MessageController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Package\PackageViewController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Payment\PaymentController;
use Systha\Core\Http\Controllers\Api\V1\Platform\PaymentMethod\PaymentMethodController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Quotation\QuotationController;
use Systha\Core\Http\Controllers\Api\V1\Platform\ServiceGroup\ServiceGroupShowController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Subscription\SubscriptionController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Subscription\SubscriptionStoreController;
use Systha\Core\Http\Controllers\Api\V1\Platform\Vendor\VendorController;
use Systha\Core\Http\Controllers\Api\V1\Platform\VendorType\VendorTypeController;


Route::group([
	'prefix' => 'api/v1/platform',
	'middleware' => ['api', 'platform.appcode'],
], function () {



	Route::get('/app-init', [AppInitializationController::class, 'appInit'])->name('app.init');
	Route::get('/about', [AppInitializationController::class, 'companyAbout'])->name('app.about');


	Route::group([
		'prefix' => 'service-groups',
	], function () {
		/**
		 * @group Platform
		 * @subgroup Static Content
		 */
		Route::get('', [ServiceGroupShowController::class, 'groupList'])->name('platform.service.group.list');
		Route::get('{group_slug}', [ServiceGroupShowController::class, 'detail'])->name('platform.service.group');
		Route::get('{group_slug}/questions', [ServiceGroupShowController::class, 'questions'])->name('platform.service.group.questions');
	});

	/**
	 * @group Platform
	 * @subgroup Auth
	 */
	Route::post('login', [AuthLoginController::class, 'login'])->name('platform.login');

	/**
	 * @group Platform
	 * @subgroup Auth
	 */
	Route::post('signup', [SignupController::class, 'signup'])->name('platform.register');

	/**
	 * @group Platform
	 * @subgroup Auth
	 */
	Route::post('password-reset', [PasswordResetController::class, 'resetPassword'])->name('platform.reset.password.client');


	Route::get('/package-image/{file_name}', [PackageViewController::class, 'packageImage'])->name('package.thumb');


	Route::group([
		'prefix' => 'vendors',
	], function () {
		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('', [VendorController::class, 'index'])->name('platform.vendor.index');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('nearby', [VendorController::class, 'nearby'])->name('platform.vendor.nearby');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('{id}/details', [VendorController::class, 'details'])->name('platform.vendor.details');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('{code}/by-code', [VendorController::class, 'detailByCode'])->name('platform.vendor.detail.by.code');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('{id}/services', [VendorController::class, 'services'])->name('platform.vendor.services');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('{code}/service-hierarchy', [VendorController::class, 'serviceHierarchy'])->name('platform.vendor.service.hierarchy');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('{code}/available-dates', [VendorController::class, 'availableDates'])->name('platform.vendor.available.dates');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('{code}/offers', [VendorController::class, 'offerList'])->name('platform.vendor.offer.list');

		/**
		 * @group Platform
		 * @subgroup Vendors
		 */
		Route::get('types', [VendorTypeController::class, 'index'])->name('platform.vendor.types');
	});

	// Route::group([
	// 	'prefix' => 'static-content',
	// ], function () {
	// 	/**
	// 	 * @group Platform
	// 	 * @subgroup Static Content
	// 	 */
	// 	Route::get('', [StaticContentController::class, 'index'])->name('platform.static.content.index');
	// });


	Route::group([
		'prefix' => 'inquiries',
	], function () {
		/**
		 * @group Platform
		 * @subgroup Inquiries
		 */
		Route::post('/store', [InquiryStoreController::class, 'store'])->name('platform.inquiry.store');

		Route::group(['middleware' => ['platform.token.refresh', 'auth:platform']], function () {
			/**
			 * @group Platform
			 * @subgroup Inquiries
			 */
			Route::get('list', [InquiryController::class, 'inquiryList'])->name('platform.inquiry.list');

			/**
			 * @group Platform
			 * @subgroup Inquiries
			 */
			Route::get('{id}', [InquiryController::class, 'show'])->name('platform.inquiry.detail');
		});
	});

	Route::group([
		'prefix' => 'inspections',
	], function () {
		/**
		 * @group Platform
		 * @subgroup Inspections
		 */
		Route::post('/store', [InspectionController::class, 'store'])->name('platform.inspection.store');

		Route::group(['middleware' => ['platform.token.refresh', 'auth:platform']], function () {
			/**
			 * @group Platform
			 * @subgroup Inspections
			 */
			Route::get('list', [InspectionController::class, 'index'])->name('platform.inspection.list');

			/**
			 * @group Platform
			 * @subgroup Inspections
			 */
			Route::get('{id}', [InspectionController::class, 'show'])->name('platform.inspection.detail');
		});
	});



	Route::group(['middleware' => ['platform.token.refresh', 'auth:platform']], function () {
		/**
		 * @group Platform
		 * @subgroup Profile
		 */
		Route::get('profile', [AuthProfileController::class, 'profile'])->name('platform.profile');

		/**
		 * @group Platform
		 * @subgroup Profile
		 */
		Route::put('profile', [AuthProfileController::class, 'updateProfile'])->name('platform.update.profile.full');

		/**
		 * @group Platform
		 * @subgroup Profile
		 */
		Route::patch('profile', [AuthProfileController::class, 'updateProfile'])->name('platform.update.profile.partial');

		/**
		 * @group Platform
		 * @subgroup Profile
		 */
		Route::put('profile-address', [AuthProfileController::class, 'updateProfileAddress'])->name('platform.update.profile.address.full');

		/**
		 * @group Platform
		 * @subgroup Profile
		 */
		Route::patch('profile-address', [AuthProfileController::class, 'updateProfileAddress'])->name('platform.update.profile.address.partial');

		/**
		 * @group Platform
		 * @subgroup Profile
		 */
		Route::put('profile-update-password', [AuthProfileController::class, 'updateProfilePassword'])->name('platform.update.profile.password');

		/**
		 * @group Platform
		 * @subgroup Profile
		 */
		Route::patch('profile-update-password', [AuthProfileController::class, 'updateProfilePassword'])->name('platform.update.profile.password');

		/**
		 * @group Platform
		 * @subgroup Auth
		 */
		Route::post('logout', [AuthProfileController::class, 'logout'])->name('platform.logout');
	});


	Route::group([
		'prefix' => 'subscriptions',
	], function () {
		/**
		 * @group Platform
		 * @subgroup Payments
		 */
		Route::post('/add', [SubscriptionStoreController::class, 'store'])->name('platform.subscription.store');
	});


	Route::group([
		'middleware' => ['platform.token.refresh', 'auth:platform']
	], function () {


		Route::group([
			'prefix' => 'quotations',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Quotations
			 */
			Route::get('', [QuotationController::class, 'index'])->name('platform.quotation.list');

			/**
			 * @group Platform
			 * @subgroup Quotations
			 */
			Route::get('{id}', [QuotationController::class, 'show'])->name('platform.quotation.detail');

			/**
			 * @group Platform
			 * @subgroup Quotations
			 */
			Route::post('{id}/confirm', [QuotationController::class, 'confirmQuotation'])->name('platform.quotation.confirm');

			Route::post('{id}/accept', [QuotationController::class, 'acceptQuotation'])->name('platform.quotation.accept');
			Route::post('{id}/reject', [QuotationController::class, 'rejectQuotation'])->name('platform.quotation.reject');
		});

		Route::group([
			'prefix' => 'payment-methods',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::post('create', [PaymentController::class, 'addPaymentMethod'])->name('platform.payment.method.create.store');

			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::post('{id}/make-default', [PaymentController::class, 'makeDefaultPaymentMethod'])->name('platform.payment.method.make.default');

			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::post('{id}/update', [PaymentController::class, 'updatePaymentMethod'])->name('platform.update.payment.method');

			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::post('{id}/delete', [PaymentController::class, 'deletePaymentMethod'])->name('platform.delete.payment.method');
		});

		Route::group([
			'prefix' => 'conversations',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Messages
			 */
			Route::get('', [MessageController::class, 'conversations'])->name('platform.conversation.list');

			/**
			 * @group Platform
			 * @subgroup Messages
			 */
			Route::get('{id}', [MessageController::class, 'show'])->name('platform.conversation.detail');

			/**
			 * @group Platform
			 * @subgroup Messages
			 */
			Route::post('{id}/send-message', [MessageController::class, 'sendMessage'])->name('platform.message.send');
		});

		Route::group([
			'prefix' => 'appointments',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Appointments
			 */
			Route::get('', [AppointmentController::class, 'index'])->name('platform.appointments');

			Route::get('today', [AppointmentController::class, 'todaysAppointments'])->name('platform.appointments.today');

			/**
			 * @group Platform
			 * @subgroup Appointments
			 */
			Route::get('/list', [AppointmentController::class, 'appointmentList'])->name('platform.appointment.list');

			/**
			 * @group Platform
			 * @subgroup Appointments
			 */
			Route::get('/{id}', [AppointmentController::class, 'show'])->name('platform.appointment.detail');

			/**
			 * @group Platform
			 * @subgroup Appointments
			 */
			Route::post('/{id}/store-card-payment', [AppointmentPaymentController::class, 'storeCardPayment'])->name('platform.appointment.card.payment');

			/**
			 * @group Platform
			 * @subgroup Appointments
			 */
			Route::post('/{id}/payment-intent', [AppointmentPaymentController::class, 'createPaymentIntent'])->name('platform.appointment.card.payment.intent');

			/**
			 * @group Platform
			 * @subgroup Appointments
			 */
			// Route::post('/{id}/cash-payment', [AppointmentPaymentController::class, 'cashPayment'])->name('platform.cash.payment.appointment.payment');
		});

		Route::group([
			'prefix' => 'subscriptions',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			// Route::post('/store', [SubscriptionStoreController::class, 'store'])->name('platform.subscription.create');

			Route::get('', [SubscriptionController::class, 'index'])->name('platform.subscription.list');

			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::get('/{id}', [SubscriptionController::class, 'show'])->name('platform.subscription.detail');

			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('platform.subscription.cancel');
		});

		Route::group([
			'prefix' => 'invoices',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Invoices
			 */
			Route::get('', [InvoiceController::class, 'index'])->name('platform.invoices.list');        // Route::get('conversations/{id}', [MessageController::class, 'show']);
			// Route::post('conversations/{id}/send-message', [MessageController::class, 'sendMessage']);

		});

		Route::group([
			'prefix' => 'payment-methods',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::get('', [PaymentMethodController::class, 'index'])->name('platform.payment-method.list');
		});

		Route::group([
			'prefix' => 'payments',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Payments
			 */
			Route::get('', [PaymentController::class, 'index'])->name('platform.payment.list');
		});

		Route::group([
			'prefix' => 'email-logs',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Messages
			 */
			Route::get('', [EmailLogController::class, 'index'])->name('platform.email.log.list');
		});



		Route::group([
			'prefix' => 'dashboard',
		], function () {
			/**
			 * @group Platform
			 * @subgroup Profile
			 */
			Route::get('summary', [DashboardController::class, 'summary'])->name('platform.dashboard.summary');
		});
	});
});
