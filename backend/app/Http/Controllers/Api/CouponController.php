<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        return response()->json(Coupon::paginate(15));
    }

    public function store(Request $request)
    {
        $record = Coupon::create($request->all());

        return response()->json($record, 201);
    }

    public function show(Coupon $coupon)
    {
        return response()->json($coupon);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($request->all());

        return response()->json($coupon->fresh());
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    public function check(Request $request)
    {
        $code = $request->query('code');
        $totalAmount = $request->query('total_amount', 0);

        if (!$code) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon code is required'
            ], 400);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon is no longer active'
            ], 400);
        }

        // Check validity dates
        $now = now();
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon is not yet valid'
            ], 400);
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon has expired'
            ], 400);
        }

        // Check usage limit
        if ($coupon->usage_limit !== null && $coupon->usage_limit <= 0) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon has reached its usage limit'
            ], 400);
        }

        // Check minimum amount
        if ($coupon->minimum_amount && $totalAmount < $coupon->minimum_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum purchase amount of $' . number_format($coupon->minimum_amount, 2) . ' required'
            ], 400);
        }

        // Calculate discount
        $discount = 0;
        if ($coupon->discount_percent) {
            $discount = ($totalAmount * $coupon->discount_percent) / 100;
        } elseif ($coupon->discount_amount) {
            $discount = $coupon->discount_amount;
        }

        // Ensure discount doesn't exceed total amount
        $discount = min($discount, $totalAmount);

        return response()->json([
            'valid' => true,
            'coupon_id' => $coupon->id,
            'discount_amount' => $discount,
            'message' => 'Coupon applied successfully'
        ]);
    }
}
