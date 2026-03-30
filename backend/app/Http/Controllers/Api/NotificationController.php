<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\NotificationRecord;
use App\Models\Shop;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('notifications')) {
            return response()->json([]);
        }

        NotificationRecord::pruneExpired();

        $user = $request->user();
        $includeAllForAdmin = filter_var($request->query('include_all'), FILTER_VALIDATE_BOOLEAN);

        $query = NotificationRecord::with([
            'user:id,name,email,profile_picture,img_url',
            'related' => fn (MorphTo $relation) => $relation->morphWith([
                Message::class => ['sender:id,name,email,profile_picture,img_url']
            ])
        ])
            ->orderByDesc('created_at');

        $query = $this->scopeVisibleNotifications($query, $user, $includeAllForAdmin);

        $shopId = $request->query('shop_id');
        if ($shopId && Schema::hasColumn('notifications', 'shop_id')) {
            $shopId = (int) $shopId;
            if ($this->isAdmin($user) || $this->userOwnsShop($user, $shopId)) {
                $query->where('shop_id', $shopId);
            } else {
                abort(403, 'Forbidden shop notifications.');
            }
        }

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $notifications = $query->get();
        return response()->json($notifications);
    }

    public function update(Request $request, NotificationRecord $notification)
    {
        $this->authorizeOwnership($request->user(), $notification);
        $data = $request->validate(['is_read' => 'required|boolean']);
        $notification->update($data);
        return response()->json($notification->refresh());
    }

    public function markAllAsRead(Request $request)
    {
        if (!Schema::hasTable('notifications')) {
            return response()->json(['updated' => 0]);
        }

        $user = $request->user();
        $query = NotificationRecord::query();
        $this->scopeVisibleNotifications($query, $user);

        $shopId = $request->input('shop_id');
        if ($shopId && Schema::hasColumn('notifications', 'shop_id')) {
            $shopId = (int) $shopId;
            if ($this->isAdmin($user) || $this->userOwnsShop($user, $shopId)) {
                $query->where('shop_id', $shopId);
            } else {
                abort(403, 'Forbidden shop notifications.');
            }
        }

        $updated = $query->update(['is_read' => true]);
        return response()->json(['updated' => $updated]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'required|in:user,shop_owner',
            'user_id' => 'required_if:target,user|nullable|integer',
            'shop_id' => 'required_if:target,shop_owner|nullable|integer',
        ]);

        if ($data['target'] === 'user') {
            $recipient = User::find($data['user_id']);
            if (!$recipient) {
                throw ValidationException::withMessages(['user_id' => ['User not found']]);
            }

            $notification = NotificationService::sendToUser(
                $recipient,
                $data['title'],
                $data['message'],
                [
                    'type' => 'message',
                    'attributes' => [
                        'role' => 'user',
                        'sent_by' => 'admin',
                    ],
                ]
            );
        } else {
            $shop = Shop::with('owner')->find($data['shop_id']);
            if (!$shop || !$shop->owner) {
                throw ValidationException::withMessages(['shop_id' => ['Shop owner not found']]);
            }

            $notification = NotificationService::sendToUser(
                $shop->owner,
                $data['title'],
                $data['message'],
                [
                    'type' => 'message',
                    'attributes' => [
                        'role' => 'shop_owner',
                        'shop_id' => $shop->id,
                        'sent_by' => 'admin',
                    ],
                ]
            );
        }

        return response()->json($notification, 201);
    }

    protected function authorizeOwnership($user, NotificationRecord $notification)
    {
        if ($notification->user_id === $user->id) {
            return true;
        }

        $userRole = $this->getUserRole($user);
        if ($userRole && $notification->role === $userRole) {
            if ($userRole === 'shop_owner' && $notification->shop_id) {
                return $this->userOwnsShop($user, $notification->shop_id);
            }
            return true;
        }

        if ($this->isAdmin($user) && $notification->role === 'admin') {
            return true;
        }

        abort(403);
    }

    protected function scopeVisibleNotifications($query, $user, bool $includeAllForAdmin = false)
    {
        if ($includeAllForAdmin && $this->isAdmin($user)) {
            return $query;
        }

        if ($this->isAdmin($user)) {
            return $query->where(function ($builder) use ($user) {
                $builder->where('user_id', $user->id)
                    ->orWhere('role', 'admin');
            });
        }

        $userRole = $this->getUserRole($user);
        $shopIds = $this->getShopIdsForUser($user);

        return $query->where(function ($builder) use ($user, $userRole, $shopIds) {
            $builder->where('user_id', $user->id);

            if ($userRole === 'shop_owner') {
                $builder->orWhere(function ($inner) use ($userRole, $shopIds) {
                    $inner->where('role', $userRole)
                        ->where(function ($scope) use ($shopIds) {
                            $scope->whereNull('shop_id');
                            if (!empty($shopIds)) {
                                $scope->orWhereIn('shop_id', $shopIds);
                            }
                        });
                });
            } elseif ($userRole) {
                $builder->orWhere('role', $userRole);
            }
        });
    }

    protected function getShopIdsForUser($user): array
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

    private function getUserRole(?User $user): ?string
    {
        if (!$user) {
            return null;
        }
        $role = strtolower((string) ($user->role ?? $user->user_type ?? ''));
        $normalized = match ($role) {
            'shop' => 'shop_owner',
            'user' => 'customer',
            default => $role,
        };
        return $normalized !== '' ? $normalized : null;
    }

    protected function isAdmin($user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->is_admin === true) {
            return true;
        }
        $role = strtolower((string) ($user->role ?? ''));
        return $role === 'admin';
    }

    protected function userOwnsShop($user, $shopId): bool
    {
        if (!$user || !$shopId) {
            return false;
        }

        return 
            \App\Models\Shop::where('id', $shopId)
                ->where('owner_id', $user->id)
                ->exists();
    }
}
