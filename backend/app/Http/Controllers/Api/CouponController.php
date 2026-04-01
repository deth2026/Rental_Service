<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ShopContext;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    use ShopContext;

    public function index(Request $request)
    {
        // Allow passing shop_id as query param for shop owners
        $queryShopId = $request->query('shop_id');
        
        // If shop_id is provided in query, use it
        if ($queryShopId) {
            $shopId = (int) $queryShopId;
        } else {
            $shopId = $this->requireShopId($request);
        }

        if (!$this->hasShopColumn()) {
            return response()->json(Coupon::paginate(15));
        }

        $query = Coupon::with('shop:id,name');
        if ($shopId) {
            $query->where('shop_id', $shopId);
        } else {
            $query->whereRaw('0 = 1');
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $payload = $this->validateCouponRequest($request);
        if (!$this->hasShopColumn()) {
            unset($payload['shop_id']);
        }
        $record = Coupon::create($payload);

        return response()->json($this->hasShopColumn() ? $record->load('shop') : $record, 201);
    }

    public function show(Coupon $coupon)
    {
        return response()->json($coupon);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $payload = $this->validateCouponRequest($request, $coupon);
        if (!$this->hasShopColumn()) {
            unset($payload['shop_id']);
        }
        $coupon->update($payload);

        return response()->json($this->hasShopColumn() ? $coupon->fresh('shop') : $coupon->fresh());
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    protected function validateCouponRequest(Request $request, ?Coupon $coupon = null): array
    {
        $codeRule = ['required', 'string', 'max:255'];
        if ($coupon) {
            $codeRule[] = Rule::unique('coupons')->ignore($coupon->id);
        } else {
            $codeRule[] = 'unique:coupons,code';
        }

        $rules = [
            'code' => $codeRule,
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
        if ($this->hasShopColumn()) {
            $rules['shop_id'] = ['nullable', 'exists:shops,id'];
        }

        if ($coupon) {
            $rules['valid_from'] = ['sometimes', 'date'];
            $rules['valid_until'] = ['sometimes', 'date'];
        } else {
            $rules['valid_from'] = ['required', 'date'];
            $rules['valid_until'] = ['required', 'date', 'after_or_equal:valid_from'];
        }

        return $request->validate($rules);
    }

    protected function hasShopColumn(): bool
    {
        return Schema::hasColumn('coupons', 'shop_id');
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
        $shopId = $request->query('shop_id');

        if ($this->hasShopColumn() && $coupon && $coupon->shop_id) {
            if ((int) $shopId !== (int) $coupon->shop_id) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This coupon is only valid for the assigned shop.'
                ], 400);
            }
        }

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
            'coupon' => $coupon->only([
                'id',
                'shop_id',
                'code',
                'discount_percent',
                'discount_amount',
                'minimum_amount',
                'valid_from',
                'valid_until',
                'usage_limit',
                'is_active',
            ]),
            'discount_amount' => $discount,
            'message' => 'Coupon applied successfully'
        ]);
    }

    /**
     * Get coupons for a specific shop (public endpoint for customers)
     */
    public function byShop(Request $request)
    {
        $shopId = $request->query('shop_id');

        if (!$shopId) {
            return response()->json([
                'message' => 'shop_id is required'
            ], 400);
        }

        $coupons = Coupon::with('shop:id,name')
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->where(function ($query) {
                $now = now();
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) {
                $now = now();
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $now);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($coupons);
    }
}
