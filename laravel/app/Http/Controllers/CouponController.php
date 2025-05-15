<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Order;
use Illuminate\Http\Request;

class CouponController
{

    public function createCoupon(Request $request) {

        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'discount' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $existingCode = Coupon::where('code', $request->code)->first();

        if($existingCode){
            return response()->json([
                'message' => 'Coupon already exists!',
            ], 409);
        }

        $coupon = Coupon::create($validated);

        return response()->json([
            'message' => 'Coupon created successfully!',
            'coupon' => $coupon
        ], 201);

    }

    public function actionCoupon(Request $request, $coupon_id){

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $coupon = Coupon::find($coupon_id)->first();

        $coupon->is_active= $validated['is_active'];
        $coupon->save();
        return response()->json([
            'message' => 'Coupon updated successfully!',

            'coupon' => $coupon
        ], 200);


    }

    public function listCoupons(){
        $coupons = Coupon::all();
        return response()->json([
            'coupons' => $coupons
        ], 200);
    }


    public function applyCoupon(Request $request ,$order_id){

        $validated = $request->validate([
            'coupon_code' => 'required|string|exists:coupons,code',
        ]);
        $user = $request->user();

        $coupon = Coupon::where('code', $validated['coupon_code'])->first();
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }
        if (!$coupon->is_active) {
            return response()->json(['message' => 'Coupon is not active'], 400);
        }

        $now = now();
        if ($coupon->start_date > $now || $coupon->end_date < $now) {
            return response()->json(['message' => 'Coupon is expired at this time'], 400);
        }

        // Check global usage limit (if any)
        // if($coupon->usage_limit !== null){
        //     $globalUsed = CouponUser::where('coupon_id', $coupon->id)->sum('used');
        //     if($globalUsed >= $coupon->usage_limit){
        //         return response()->json(['message' => 'Coupon usage limit reached'], 400);
        //     }
        // }

        $order = Order::where('id', $order_id)->where('user_id', $user->id)->first();

        if ($order->coupon_id) {
            return response()->json(['message' => 'Coupon already applied to this order'], 400);
        }

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Coupon can only be applied to pending orders'], 400);
        }

        $couponUser = CouponUser::where("user_id", $user->id)
            ->where("coupon_id", $coupon->id)
            ->first();

        if ($couponUser) {
            if ($couponUser->used >= $couponUser->limit) {
                return response()->json(['message' => 'Coupon usage limit reached for this user'], 400);
            }
            $couponUser->increment('used');
        } else {
            // Create a new coupon user record
            $couponUser = CouponUser::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'used' => 1,
                'limit' => $coupon->usage_limit,
            ]);
        }




        $discountAmount = ($order->total_price * $coupon->discount) / 100;
        $order->final_price = $order->total_price - $discountAmount;
        $order->coupon_id = $coupon->id;
        $order->save();

    return response()->json([
        'message' => 'Coupon applied successfully',
        'discount_amount' => $discountAmount,
        'final_price' => $order->final_price,
    ]);
    // return response()->json(['message' => 'Coupon applied successfully']);
    }
}
