<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Get all vehicle ratings with feedback (for shop owner review page)
     */
    public function vehicleRatings(Request $request)
    {
        $shopId = $request->user()?->shop?->id;
        
        // Get ratings with vehicle and user information
        $ratings = Rating::with(['vehicle', 'user', 'booking'])
            ->when($shopId, function($query) use ($shopId) {
                return $query->where('shop_id', $shopId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($ratings);
    }

    /**
     * Get ratings grouped by vehicle (summary) - only vehicles with ratings
     */
    public function vehicleRatingsSummary(Request $request)
    {
        $shopId = $request->user()?->shop?->id;
        
        // Get only vehicles that have ratings
        $vehicles = Vehicle::whereHas('directRatings', function($query) use ($shopId) {
                if ($shopId) {
                    $query->where('shop_id', $shopId);
                }
            })
            ->withCount(['directRatings' => function($query) use ($shopId) {
                if ($shopId) {
                    $query->where('shop_id', $shopId);
                }
            }])
            ->with(['directRatings' => function($query) use ($shopId) {
                if ($shopId) {
                    $query->where('shop_id', $shopId);
                }
                $query->with('user');
                $query->orderBy('created_at', 'desc');
            }])
            ->when($shopId, function($query) use ($shopId) {
                return $query->where('shop_id', $shopId);
            })
            ->get()
            ->map(function($vehicle) {
                $vehicleRatings = $vehicle->directRatings;
                $avgRating = $vehicleRatings->avg('rating');
                $totalRatings = $vehicleRatings->count();
                
                return [
                    'id' => $vehicle->id,
                    'vehicle_name' => $vehicle->brand . ' ' . $vehicle->model,
                    'vehicle_image' => $vehicle->image_url_full,
                    'average_rating' => $avgRating ? round($avgRating, 1) : 0,
                    'total_ratings' => $totalRatings,
                    'ratings' => $vehicleRatings->map(function($r) {
                        return [
                            'id' => $r->id,
                            'rating' => $r->rating,
                            'comment' => $r->comment,
                            'user_name' => $r->user?->name ?? 'Anonymous',
                            'user_profile_picture' => $r->user?->avatar_url,
                            'created_at' => $r->created_at,
                        ];
                    })
                ];
            });

        return response()->json($vehicles);
    }
    public function store(Request $request, Booking $booking)
    {
        $user = Auth::user();
        if (!$user || $booking->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'completed') {
            return response()->json(['error' => 'Only completed bookings can be rated'], 422);
        }

        if ($booking->rating) {
            return response()->json(['error' => 'Booking already rated'], 409);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $rating = Rating::create([
            'booking_id' => $booking->id,
            'vehicle_id' => $booking->vehicle_id,
            'shop_id' => $booking->shop_id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($rating);
    }
}
