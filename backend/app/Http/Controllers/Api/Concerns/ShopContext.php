<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Shop;
use Illuminate\Http\Request;

trait ShopContext
{
    protected function getShopIdFromUser(Request $request): ?int
    {
        $shop = $this->getUserShop($request);
        return $shop?->id;
    }

    protected function getShopIdsFromUser(Request $request): array
    {
        $user = $request->user();
        if (!$user) {
            return [];
        }

        return Shop::where('owner_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    protected function getUserShop(Request $request): ?Shop
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        if ($user->relationLoaded('shop')) {
            return $user->shop;
        }

        return Shop::where('owner_id', $user->id)->first();
    }

    protected function requireShopId(Request $request): ?int
    {
        $queryId = $request->query('shop_id');
        $userShopIds = $this->getShopIdsFromUser($request);
        if ($queryId && in_array((int) $queryId, $userShopIds, true)) {
            return (int) $queryId;
        }

        $shopId = $this->getShopIdFromUser($request);
        if ($shopId) {
            return $shopId;
        }

        if (!empty($userShopIds)) {
            return $userShopIds[0];
        }

        return null;
    }
}
