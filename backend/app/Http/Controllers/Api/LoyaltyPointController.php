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

    public function store(Request $_request)
    {
        $record = LoyaltyPoint::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(LoyaltyPoint $loyaltyPoint)
    {
        return response()->json($loyaltyPoint);
    }

    public function update(Request $_request, LoyaltyPoint $loyaltyPoint)
    {
        $loyaltyPoint->update($_request->all());

        return response()->json($loyaltyPoint->fresh());
    }

    public function destroy(LoyaltyPoint $loyaltyPoint)
    {
        $loyaltyPoint->delete();

        return response()->json(['message' => 'LoyaltyPoint deleted successfully']);
    }
}
