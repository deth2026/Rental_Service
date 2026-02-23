<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        return response()->json(Shop::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = Shop::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(Shop $shop)
    {
        return response()->json($shop);
    }

    public function update(Request $_request, Shop $shop)
    {
        $shop->update($_request->all());

        return response()->json($shop->fresh());
    }

    public function destroy(Shop $shop)
    {
        $shop->delete();

        return response()->json(['message' => 'Shop deleted successfully']);
    }
}
