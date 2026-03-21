<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->user()?->shop?->id;
        
        $feedback = Feedback::with(['user', 'booking', 'booking.vehicle'])
            ->when($shopId, function($query) use ($shopId) {
                return $query->where('shop_id', $shopId);
            })
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
