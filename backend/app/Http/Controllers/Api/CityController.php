<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->query('per_page', 50), 200));
        $activeOnly = filter_var($request->query('active_only', false), FILTER_VALIDATE_BOOLEAN);
        $all = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);

        $query = City::query()->orderBy('name');

        if ($activeOnly && \Illuminate\Support\Facades\Schema::hasColumn('cities', 'status')) {
            $query->where('status', 'active');
        }

        if ($all) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $_request)
    {
        $payload = $_request->all();
        if (Schema::hasColumn('cities', 'status') && !array_key_exists('status', $payload)) {
            $payload['status'] = 'active';
        }
        if (Schema::hasColumn('cities', 'shop_id') && !array_key_exists('shop_id', $payload)) {
            $payload['shop_id'] = 0;
        }

        $record = City::create($payload);

        return response()->json($record, 201);
    }

    public function show(City $city)
    {
        return response()->json($city);
    }

    public function update(Request $_request, City $city)
    {
        $city->update($_request->all());

        return response()->json($city->fresh());
    }

    public function destroy(City $city)
    {
        $city->delete();

        return response()->json(['message' => 'City deleted successfully']);
    }
}
