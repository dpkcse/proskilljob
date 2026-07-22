<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\CandidatePlan\Http\Controllers\CandidatePlanController;
use Modules\CandidatePlan\Http\Controllers\Frontend\CandidatePlanController as FrontendCandidatePlanController;
use Modules\CandidatePlan\Http\Controllers\Payment\CandidatePayPalController;
use Modules\CandidatePlan\Http\Controllers\Payment\CandidatePaystackController;
use Modules\CandidatePlan\Http\Controllers\Payment\CandidateRazorpayController;
use Modules\CandidatePlan\Http\Controllers\Payment\CandidateStripeController;

Route::middleware(['auth:admin', 'set_lang'])->group(function () {
    Route::prefix('admin/candidateplan')->name('module.candidateplan.')->group(function () {
        Route::get('candidateplan', [CandidatePlanController::class, 'index'])->name('index');
        Route::get('candidateplan/create', [CandidatePlanController::class, 'create'])->name('create');
        Route::post('candidateplan', [CandidatePlanController::class, 'store'])->name('store');
        Route::get('candidateplan/{id}', [CandidatePlanController::class, 'show'])->name('show');
        Route::get('candidateplan/{id}/edit', [CandidatePlanController::class, 'edit'])->name('edit');
        Route::put('candidateplan/{id}', [CandidatePlanController::class, 'update'])->name('update');
        Route::delete('candidateplan/{id}', [CandidatePlanController::class, 'destroy'])->name('destroy');

        // Orders/Transactions Routes
        Route::get('orders', [CandidatePlanController::class, 'orders'])->name('orders');
        Route::get('orders/{id}', [CandidatePlanController::class, 'showOrder'])->name('orders.show');
    });
});

// Frontend Routes
Route::middleware(['web', 'set_lang'])->group(function () {
    // Plan Routes
    Route::get('candidate/plan', [FrontendCandidatePlanController::class, 'pricing'])->name('candidate.plan');
    Route::get('candidate/my-plan', [FrontendCandidatePlanController::class, 'myPlan'])->name('candidate.my.plan');
    Route::post('candidate/plan/purchase/free', [FrontendCandidatePlanController::class, 'purchaseFreePlan'])->name('candidate.plan.purchase.free');
    Route::get('candidate/plan/{id}', [FrontendCandidatePlanController::class, 'planDetails'])->name('candidate.plan.details');

    // Invoice Routes
    Route::get('candidate/transaction/invoice/{id}', [FrontendCandidatePlanController::class, 'viewInvoice'])->name('candidate.transaction.invoice.view');
    Route::post('candidate/transaction/invoice/download/{id}', [FrontendCandidatePlanController::class, 'downloadInvoice'])->name('candidate.transaction.invoice.download');

    // Payment Routes
    Route::prefix('candidate/payment')->name('candidate.')->group(function () {
        // PayPal Routes
        Route::post('paypal/process', [CandidatePayPalController::class, 'processTransaction'])->name('paypal.process');
        Route::get('paypal/success', [CandidatePayPalController::class, 'successTransaction'])->name('paypal.success');
        Route::get('paypal/cancel', [CandidatePayPalController::class, 'cancelTransaction'])->name('paypal.cancel');

        // Stripe Routes
        Route::post('stripe/process', [CandidateStripeController::class, 'processTransaction'])->name('stripe.process');

        // Razorpay Routes
        Route::post('razorpay/process', [CandidateRazorpayController::class, 'processTransaction'])->name('razorpay.process');
        Route::post('razorpay/success', [CandidateRazorpayController::class, 'successTransaction'])->name('razorpay.success');
        Route::post('razorpay/failure', [CandidateRazorpayController::class, 'failureTransaction'])->name('razorpay.failure');

        // Paystack Routes
        Route::post('paystack/process', [CandidatePaystackController::class, 'processTransaction'])->name('paystack.process');
        Route::get('paystack/success', [CandidatePaystackController::class, 'successTransaction'])->name('paystack.success');
        Route::get('paystack/cancel', [CandidatePaystackController::class, 'cancelTransaction'])->name('paystack.cancel');

        // Coupon Routes
        Route::post('apply-coupon', [FrontendCandidatePlanController::class, 'applyCoupon'])->name('apply.coupon');
    });
});

// Admin Routes
Route::middleware(['auth:admin', 'set_lang'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('candidate-plans', CandidatePlanController::class);
});
