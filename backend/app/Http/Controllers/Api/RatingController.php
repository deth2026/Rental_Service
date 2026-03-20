<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
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
            'shop_id' => $booking->shop_id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($rating);
    }
}
