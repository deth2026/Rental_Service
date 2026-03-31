<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ShopContext;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    use ShopContext;

    public function index(Request $request)
    {
        // First check if shop_id is provided in query params
        $requestedShopId = $request->query('shop_id');
        
        // Get user's owned shop IDs
        $userShopIds = $this->getShopIdsFromUser($request);
        
        // Determine which shop IDs to use for filtering
        $shopIdsToFilter = [];
        
        if ($requestedShopId) {
            // If specific shop_id is requested, verify user owns it
            if (!empty($userShopIds) && in_array($requestedShopId, $userShopIds)) {
                $shopIdsToFilter = [(int)$requestedShopId];
            } else {
                // User doesn't own this shop, return empty
                $shopIdsToFilter = [0]; // Non-existent ID to return empty
            }
        } else {
            // No specific shop_id, use user's owned shops
            $shopIdsToFilter = $userShopIds;
        }

        $feedback = Feedback::with(['user', 'booking', 'booking.vehicle'])
            ->when(!empty($shopIdsToFilter), function($query) use ($shopIdsToFilter) {
                return $query->whereIn('shop_id', $shopIdsToFilter);
            })
            ->when(empty($shopIdsToFilter), function($query) {
                return $query->whereRaw('0 = 1');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Transform the collection to include vehicle_name and user profile picture
        $feedback->getCollection()->transform(function ($item) {
            $item->vehicle_name = $item->booking && $item->booking->vehicle 
                ? ($item->booking->vehicle->name ?: $item->booking->vehicle->brand . ' ' . $item->booking->vehicle->model)
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
}
