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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'plate' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Available,Rented,Maintenance',
        ]);

        $data = $request->all();
        
        // Handle photos - could be base64 array or regular array
        $photosData = $data['photos'] ?? [];
        if (is_array($photosData)) {
            // Filter out non-base64 data (file names that may have been passed)
            $photosData = array_filter($photosData, function($photo) {
                return strpos($photo, 'data:') === 0 || strpos($photo, 'base64,') !== false;
            });
        }
        
        // Map frontend fields to database fields
        $vehicleData = [
            'name' => $data['name'] ?? '',
            'type' => $data['category'] ?? $data['type'] ?? '',
            'brand' => $data['brand'] ?? '',
            'model' => $data['model'] ?? '',
            'plate_number' => $data['plate'] ?? '',
            'price_per_day' => $data['price'] ?? 0,
            'status' => $data['status'] ?? 'Available',
            'fuel_type' => $data['fuel'] ?? '',
            'transmission' => $data['transmission'] ?? '',
            'description' => $data['description'] ?? '',
            'image_url' => !empty($photosData) ? $photosData[0] : ($data['previewUrl'] ?? $data['image'] ?? ''),
            'photos' => json_encode(array_values($photosData))
        ];

        $record = Vehicle::create($vehicleData);

        return response()->json($record, 201);
    }

    public function show(Vehicle $vehicle)
    {
        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'plate' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Available,Rented,Maintenance',
        ]);

        $data = $request->all();
        
        // Handle photos - could be base64 array or regular array
        $photosData = $data['photos'] ?? null;
        if ($photosData !== null && is_array($photosData)) {
            // Filter out non-base64 data (file names that may have been passed)
            $photosData = array_filter($photosData, function($photo) {
                return strpos($photo, 'data:') === 0 || strpos($photo, 'base64,') !== false;
            });
        } else {
            $photosData = json_decode($vehicle->photos ?? '[]', true);
        }
        
        // Map frontend fields to database fields
        $vehicleData = [
            'name' => $data['name'] ?? $vehicle->name,
            'type' => $data['category'] ?? $data['type'] ?? $vehicle->type,
            'brand' => $data['brand'] ?? $vehicle->brand,
            'model' => $data['model'] ?? $vehicle->model,
            'plate_number' => $data['plate'] ?? $vehicle->plate_number,
            'price_per_day' => $data['price'] ?? $vehicle->price_per_day,
            'status' => $data['status'] ?? $vehicle->status,
            'fuel_type' => $data['fuel'] ?? $vehicle->fuel_type,
            'transmission' => $data['transmission'] ?? $vehicle->transmission,
            'description' => $data['description'] ?? $vehicle->description,
            'image_url' => !empty($photosData) ? $photosData[0] : ($data['previewUrl'] ?? $vehicle->image_url),
            'photos' => json_encode(array_values($photosData))
        ];

        $vehicle->update($vehicleData);

        return response()->json($vehicle->fresh());
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }
}
