<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecord;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = NotificationRecord::with(['user:id,name,email,profile_picture,img_url'])
            ->where(function ($builder) use ($user) {
                $builder->where('user_id', $user->id);
                if ($user->role) {
                    $builder->orWhere('role', $user->role);
                }
            })
            ->orderByDesc('created_at');

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
        $user = $request->user();
        $query = NotificationRecord::where(function ($builder) use ($user) {
            $builder->where('user_id', $user->id);
            if ($user->role) {
                $builder->orWhere('role', $user->role);
            }
        });
        $updated = $query->update(['is_read' => true]);
        return response()->json(['updated' => $updated]);
    }

    protected function authorizeOwnership($user, NotificationRecord $notification)
    {
        if ($notification->user_id === $user->id) {
            return true;
        }
        if ($notification->role && $notification->role === $user->role) {
            return true;
        }
        abort(403);
    }
}
