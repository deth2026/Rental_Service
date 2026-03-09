<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class VehicleController extends Controller
{
    private function vehicleHasColumn(string $column): bool
    {
        static $columns = null;
        if ($columns === null) {
            $columns = Schema::getColumnListing('vehicles');
        }

        return in_array($column, $columns, true);
    }

    private function isBase64Image(?string $value): bool
    {
        return is_string($value) && preg_match('/^data:image\/(\w+);base64,/', $value) === 1;
    }

    private function saveBase64Image(string $dataUrl): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
            return null;
        }

        $extension = strtolower($matches[1]);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $raw = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary = base64_decode($raw, true);
        if ($binary === false) {
            return null;
        }

        $directory = storage_path('app/public/vehicles');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = time() . '_' . uniqid() . '.' . $extension;
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $binary);

        return 'vehicles/' . $filename;
    }

    private function normalizePhotos($photosData): array
    {
        if (is_string($photosData)) {
            $decoded = json_decode($photosData, true);
            if (is_array($decoded)) {
                $photosData = $decoded;
            }
        }

        if (!is_array($photosData)) {
            return [];
        }

        $normalized = [];
        foreach ($photosData as $photo) {
            if (!is_string($photo) || trim($photo) === '') {
                continue;
            }

            $value = trim($photo);
            if ($this->isBase64Image($value)) {
                $stored = $this->saveBase64Image($value);
                if ($stored) {
                    $normalized[] = $stored;
                }
                continue;
            }

            $trimmed = ltrim($value, '/');
            if (
                filter_var($value, FILTER_VALIDATE_URL) ||
                str_starts_with($trimmed, 'vehicles/') ||
                str_starts_with($trimmed, 'storage/')
            ) {
                $normalized[] = $trimmed;
            }
        }

        return array_values($normalized);
    }

    private function resolveSingleImageUrl(array $data, array $photosData, ?string $fallback = ''): string
    {
        if (!empty($photosData)) {
            return $photosData[0];
        }

        foreach (['previewUrl', 'image', 'image_url'] as $key) {
            $candidate = $data[$key] ?? null;
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $candidate = trim($candidate);
            if ($this->isBase64Image($candidate)) {
                $stored = $this->saveBase64Image($candidate);
                if ($stored) {
                    return $stored;
                }
                continue;
            }

            $trimmed = ltrim($candidate, '/');
            if (
                filter_var($candidate, FILTER_VALIDATE_URL) ||
                str_starts_with($trimmed, 'vehicles/') ||
                str_starts_with($trimmed, 'storage/')
            ) {
                return $trimmed;
            }
        }

        return (string) ($fallback ?? '');
    }

    public function index(Request $request)
    {
        // If user is authenticated, filter vehicles by their shop
        $user = $request->user();
        
        if ($user && $user->role === 'shop_owner') {
            // Get all shops owned by this user
            $shopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id')->toArray();
            
            // If user has no shops, return empty result
            if (empty($shopIds)) {
                return response()->json([
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0
                ]);
            }
            
            // Return only vehicles from user's shops
            $vehicles = Vehicle::whereIn('shop_id', $shopIds)->paginate(15);
            return VehicleResource::collection($vehicles);
        }
        
        // For admin or unauthenticated users, return all vehicles
        $vehicles = Vehicle::paginate(15);
        return VehicleResource::collection($vehicles);
    }

    public function store(Request $request)
    {
        // Log all request data for debugging
        \Log::info('=== Vehicle Store Request ===');
        \Log::info('Request all data:', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'plate' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Available,Rented,Maintenance',
            'shop_id' => 'nullable|integer',
        ]);

        $data = $request->all();
        
        // Get authenticated user's shop and automatically assign shop_id
        $user = $request->user();
        $shopId = $data['shop_id'] ?? null;
        
        // If no shop_id provided or user is authenticated, try to get their shop
        if (!$shopId && $user) {
            $userShop = \App\Models\Shop::where('owner_id', $user->id)->first();
            if ($userShop) {
                $shopId = $userShop->id;
            }
        }
        
        // Persist base64 photos as files and store only lightweight paths/URLs.
        $photosData = $this->normalizePhotos($data['photos'] ?? []);
        
        // Map frontend fields to database fields
        $vehicleData = [
            'shop_id' => $shopId,
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
            'image_url' => $this->resolveSingleImageUrl($data, $photosData, '')
        ];

        if ($this->vehicleHasColumn('year')) {
            $vehicleData['year'] = (int) ($data['year'] ?? date('Y'));
        }

        if ($this->vehicleHasColumn('photos')) {
            $vehicleData['photos'] = json_encode(array_values($photosData));
        }

        \Log::info('Vehicle data to create:', $vehicleData);

        try {
            $record = Vehicle::create($vehicleData);
            \Log::info('Vehicle created successfully with ID:', ['id' => $record->id]);
            return response()->json(new VehicleResource($record), 201);
        } catch (\Exception $e) {
            \Log::error('Error creating vehicle:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to create vehicle. Please try again.'], 500);
        }
    }

    public function show(Vehicle $vehicle)
    {
        return response()->json(new VehicleResource($vehicle));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        // Check if user is authorized to update this vehicle
        $user = $request->user();
        if ($user && $user->role !== 'admin') {
            // Get user's shop IDs
            $userShopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id')->toArray();
            
            // Check if vehicle belongs to user's shop
            if (!in_array($vehicle->shop_id, $userShopIds)) {
                return response()->json([
                    'message' => 'Unauthorized. You can only update your own shop\'s vehicles.',
                ], 403);
            }
        }
        
        $request->validate([
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'plate' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Available,Rented,Maintenance',
            'shop_id' => 'nullable|integer',
        ]);
        
        $data = $request->all();
        
        // Handle photos - persist base64 as files and keep path references
        $photosData = $data['photos'] ?? null;
        if ($photosData !== null && is_array($photosData)) {
            $photosData = $this->normalizePhotos($photosData);
        } else {
            $photosData = json_decode($vehicle->photos ?? '[]', true);
        }
        
        // Map frontend fields to database fields
        $vehicleData = [
            'shop_id' => $data['shop_id'] ?? $vehicle->shop_id,
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
            'image_url' => $this->resolveSingleImageUrl($data, $photosData, $vehicle->image_url)
        ];

        if ($this->vehicleHasColumn('year')) {
            $vehicleData['year'] = (int) ($data['year'] ?? $vehicle->year ?? date('Y'));
        }

        if ($this->vehicleHasColumn('photos')) {
            $vehicleData['photos'] = json_encode(array_values($photosData));
        }

        try {
            $vehicle->update($vehicleData);
            return response()->json(new VehicleResource($vehicle->fresh()));
        } catch (\Exception $e) {
            \Log::error('Error updating vehicle:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to update vehicle. Please try again.'], 500);
        }
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        // Check if user is authorized to delete this vehicle
        $user = $request->user();
        if ($user && $user->role !== 'admin') {
            // Get user's shop IDs
            $userShopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id')->toArray();
            
            // Check if vehicle belongs to user's shop
            if (!in_array($vehicle->shop_id, $userShopIds)) {
                return response()->json([
                    'message' => 'Unauthorized. You can only delete your own shop\'s vehicles.',
                ], 403);
            }
        }
        
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }
}
