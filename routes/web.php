<?php

use App\Http\Controllers\Frontend\CouponController;
use Illuminate\Support\Facades\Route;

include base_path('routes/admin.php');
include base_path('routes/website.php');
include base_path('routes/payment.php');

Route::fallback(function () {
    if (request()->is('419')) {
        return view('errors.419');
    }

    return view('errors.404');
});

Route::post('/apply-coupon', [CouponController::class, 'apply'])->name('apply.coupon');
