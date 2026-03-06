<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class LoyaltyPointController extends Controller
{
    public function index()
    {
        return response()->json(LoyaltyPoint::paginate(15));
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
