<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Shop;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::with('shop.owner')->orderByDesc('id');
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? ''));

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->integer('shop_id'));
        }

        if ($request->filled('code')) {
            $query->whereRaw('LOWER(code) = ?', [strtolower((string) $request->query('code'))]);
        }

        if ($user && $role === 'shop_owner') {
            $shopIds = Shop::where('owner_id', $user->id)->pluck('id')->all();
            $query->whereIn('shop_id', $shopIds ?: [0]);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'shop_id' => 'nullable|integer|exists:shops,id',
            'code' => 'required|string|max:255',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'required|date',
            'usage_limit' => 'nullable|integer|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $payload['shop_id'] = $this->resolveShopId($request, $payload['shop_id'] ?? null);

        $record = Coupon::create($payload);
        $record->loadMissing('shop.owner');

        try {
            NotificationService::couponCreated($record);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send coupon notifications.', [
                'coupon_id' => $record->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json($record->load('shop.owner'), 201);
    }

    public function show(Coupon $coupon)
    {
        return response()->json($coupon->load('shop.owner'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $payload = $request->validate([
            'shop_id' => 'nullable|integer|exists:shops,id',
            'code' => 'sometimes|string|max:255',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $resolvedShopId = $this->resolveShopId($request, $payload['shop_id'] ?? $coupon->shop_id, $coupon);
        $payload['shop_id'] = $resolvedShopId;

        $coupon->update($payload);

        return response()->json($coupon->fresh()->load('shop.owner'));
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    private function resolveShopId(Request $request, ?int $requestedShopId = null, ?Coupon $coupon = null): ?int
    {
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? ''));

        if ($role === 'shop_owner') {
            $shop = Shop::where('owner_id', $user->id)->first();

            if (!$shop) {
                throw ValidationException::withMessages([
                    'shop_id' => ['No shop found for this shop owner.'],
                ]);
            }

            if ($coupon && $coupon->shop_id && (int) $coupon->shop_id !== (int) $shop->id) {
                throw ValidationException::withMessages([
                    'shop_id' => ['You can only manage coupons for your own shop.'],
                ]);
            }

            return (int) $shop->id;
        }

        return $requestedShopId;
    }
}
