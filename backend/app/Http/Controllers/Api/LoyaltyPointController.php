<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class LoyaltyPointController extends Controller
{
    public function index()
    {
        $loyaltyPoints = LoyaltyPoint::with('user')->get()->map(function ($lp) {
            $user = $lp->user;
            $bookingsCount = Booking::where('user_id', $lp->user_id)->count();
            
            // Determine status based on points
            $status = 'Silver';
            if ($lp->points >= 400) {
                $status = 'Gold';
            }
            
            return [
                'id' => $lp->id,
                'user_id' => $lp->user_id,
                'name' => $user ? $user->name : 'Unknown',
                'email' => $user ? $user->email : 'N/A',
                'points' => $lp->points,
                'bookings' => $bookingsCount,
                'status' => $status,
                'updated_at' => $lp->updated_at
            ];
        });
        
        return response()->json($loyaltyPoints);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $loyaltyPoint = LoyaltyPoint::create($validated);

        return response()->json($loyaltyPoint, 201);
    }

    public function show(LoyaltyPoint $loyaltyPoint)
    {
        return response()->json($loyaltyPoint);
    }

    public function update(Request $request, LoyaltyPoint $loyaltyPoint)
    {
        $validated = $request->validate([
            'points' => 'sometimes|required|integer',
            'description' => 'nullable|string',
        ]);

        $loyaltyPoint->update($validated);

        return response()->json($loyaltyPoint);
    }

    public function destroy(LoyaltyPoint $loyaltyPoint)
    {
        $loyaltyPoint->delete();

        return response()->json(['message' => 'Loyalty point deleted successfully']);
    }
}
