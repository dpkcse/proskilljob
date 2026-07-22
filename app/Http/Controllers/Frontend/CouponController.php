<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Modules\Plan\Entities\Plan;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'plan_id' => 'required|integer',
        ]);

        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('status', 1)
            ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired coupon.']);
        }

        // Ensure 'used' is initialized if it's null
        if (is_null($coupon->used)) {
            $coupon->used = 0;
        }

        if ($coupon->max_uses && $coupon->used >= $coupon->max_uses) {
            return response()->json(['success' => false, 'message' => 'Coupon usage limit reached.']);
        }

        $plan = Plan::findOrFail($request->plan_id);

        $discount = $coupon->type === 'percent'
            ? ($plan->price * $coupon->value / 100)
            : $coupon->value;

        $discount = min($discount, $plan->price);
        $newTotal = $plan->price - $discount;

        $coupon->used++;
        $coupon->save();

        // Optionally: store coupon in session for checkout
        session([
            'applied_coupon' => $coupon->code,
            'coupon_discount' => $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied! Discount: '.currencyPosition($discount, true),
            'discount' => $discount,
            'new_total' => currencyPosition($newTotal, true),
        ]);
    }
}
