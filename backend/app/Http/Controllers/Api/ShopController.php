<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with('owner:id,name,email')
            ->orderByDesc('id')
            ->get();

        return response()->json($shops);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'city_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_reviews' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // A shop owner can only create shops for themselves.
        $payload['owner_id'] = $request->user()?->id;

        $record = Shop::create($payload);

        return response()->json($record, 201);
    }

    public function show(Shop $shop)
    {
        return response()->json($shop->load('owner:id,name,email'));
    }

    public function update(Request $request, Shop $shop)
    {
        $user = $request->user();
        if ($user && $user->role !== 'admin' && (int) $shop->owner_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only update your own shops.',
            ], 403);
        }

        $payload = $request->validate([
            'city_id' => 'nullable|integer',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_reviews' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $shop->update($payload);

        return response()->json($shop->fresh());
    }

    public function destroy(Request $request, Shop $shop)
    {
        $user = $request->user();
        if ($user && $user->role !== 'admin' && (int) $shop->owner_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only delete your own shops.',
            ], 403);
        }

        $shop->delete();

        return response()->json(['message' => 'Shop deleted successfully']);
    }
}
