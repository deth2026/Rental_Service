<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Shop;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        [$shopId, $restrictByShop] = $this->resolveShopScope($request);

        $feedbackQuery = Feedback::with(['user', 'booking', 'booking.vehicle']);
        if ($restrictByShop) {
            if ($shopId !== null) {
                $feedbackQuery->where('shop_id', $shopId);
            } else {
                $feedbackQuery->whereRaw('0 = 1');
            }
        } elseif ($shopId !== null) {
            $feedbackQuery->where('shop_id', $shopId);
        }

        $feedback = $feedbackQuery
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Transform the collection to include vehicle_name and user profile picture
        $feedback->getCollection()->transform(function ($item) {
            $item->vehicle_name = $item->booking && $item->booking->vehicle 
                ? $item->booking->vehicle->name 
                : null;
            // Include user profile picture
            if ($item->user) {
                $item->user_name = $item->user->name;
                $item->user_profile_picture = $item->user->avatar_url;
            }
            return $item;
        });
        
        return response()->json($feedback);
    }

    public function store(Request $_request)
    {
        $record = Feedback::create($_request->all());
        $record->loadMissing(['user', 'booking.vehicle', 'booking.shop.owner']);

        try {
            NotificationService::feedbackSubmitted($record);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send feedback notifications.', [
                'feedback_id' => $record->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json($record, 201);
    }

    public function show(Feedback $feedback)
    {
        return response()->json($feedback);
    }

    public function update(Request $_request, Feedback $feedback)
    {
        $feedback->update($_request->all());

        return response()->json($feedback->fresh());
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted successfully']);
    }

    private function resolveShopScope(Request $request): array
    {
        $user = $request->user();
        $shopId = $request->query('shop_id');
        $shopId = $shopId !== null ? (int) $shopId : null;
        $ownedShopIds = $this->getOwnedShopIds($user);

        if ($shopId !== null && $shopId > 0) {
            if ($this->isAdmin($user) || in_array($shopId, $ownedShopIds, true)) {
                return [$shopId, true];
            }
            abort(403, 'Unauthorized shop access.');
        }

        if ($this->isShopOwner($user)) {
            return [$ownedShopIds[0] ?? null, true];
        }

        return [null, false];
    }

    private function getOwnedShopIds($user): array
    {
        if (!$user) {
            return [];
        }

        static $cache = [];
        $cacheKey = $user->id;
        if ($cacheKey !== null && array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $ids = Shop::where('owner_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($cacheKey !== null) {
            $cache[$cacheKey] = $ids;
        }

        return $ids;
    }

    private function normalizeRole($role): string
    {
        $lower = strtolower((string) ($role ?? ''));
        return match ($lower) {
            'shop', 'owner' => 'shop_owner',
            'user' => 'customer',
            default => $lower,
        };
    }

    private function isShopOwner($user): bool
    {
        $normalized = $this->normalizeRole($user?->role ?? '');
        return $normalized === 'shop_owner';
    }

    private function isAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if (($user->is_admin ?? false) === true) {
            return true;
        }

        return $this->normalizeRole($user->role) === 'admin';
    }
}
