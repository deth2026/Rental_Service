<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return response()->json(Vehicle::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = Vehicle::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(Vehicle $vehicle)
    {
        return response()->json($vehicle);
    }

    public function update(Request $_request, Vehicle $vehicle)
    {
        $vehicle->update($_request->all());

        return response()->json($vehicle->fresh());
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }
}
