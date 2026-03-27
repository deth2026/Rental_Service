<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    private function ratingAwareVehicleQuery()
    {
        $query = Vehicle::with('shop');

        if (Schema::hasTable('ratings')) {
            $query->withAvg('ratings', 'rating')->withCount('ratings');
        }

        return $query;
    }

    private function filterVehicleDataBySchema(array $data): array
    {
        $allowed = Schema::getColumnListing('vehicles');
        return array_intersect_key($data, array_flip($allowed));
    }

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

    private function normalizeNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeNullableNumber($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
        }

        return is_numeric($value) ? $value + 0 : $value;
    }

    private function normalizeVehicleRequestData(array $data): array
    {
        if (array_key_exists('rider_details', $data)) {
            $data['rider_details'] = $this->normalizeNullableString($data['rider_details']);
        }

        foreach (['price', 'insurance_fee', 'taxes_fee', 'total_vehicles'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeNullableNumber($data[$field]);
            }
        }

        return $data;
    }

    private function currentRole(Request $request): string
    {
        return strtolower((string) ($request->user()?->role ?? $request->user()?->user_type ?? ''));
    }

    public function index(Request $request)
    {
        // Check if shop_id is provided in the query parameters
        $shopId = $request->query('shop_id');

        if ($shopId) {
            $query = $this->ratingAwareVehicleQuery()->where('shop_id', $shopId);
            return VehicleResource::collection($query->paginate(15));
        }

        $user = $request->user();

        if ($user && $this->currentRole($request) === 'shop_owner') {
            $shopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id')->toArray();

            if (empty($shopIds)) {
                return response()->json([
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0
                ]);
            }

            $query = $this->ratingAwareVehicleQuery()->whereIn('shop_id', $shopIds);
            return VehicleResource::collection($query->paginate(15));
        }

        $query = $this->ratingAwareVehicleQuery();
        return VehicleResource::collection($query->paginate(15));
    }

    public function store(Request $request)
    {
        // Log all request data for debugging
        Log::info('=== Vehicle Store Request ===');
        Log::info('Request all data:', $request->all());

        $request->merge($this->normalizeVehicleRequestData($request->all()));
        
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'plate' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Available,Rented,Maintenance',
            'shop_id' => 'nullable|integer',
            'total_vehicles' => 'nullable|integer|min:0',
            'rider_details' => 'nullable|string|max:255',
            'insurance_fee' => 'nullable|numeric|min:0',
            'taxes_fee' => 'nullable|numeric|min:0',
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
        
        $resolvedModel = $data['model'] ?? '';
        if ($resolvedModel === '' && !$this->vehicleHasColumn('name')) {
            $resolvedModel = $data['name'] ?? '';
        }

        $plateValue = $data['plate'] ?? '';

        // Map frontend fields to database fields
        $vehicleData = [
            'shop_id' => $shopId,
            'type' => $data['category'] ?? $data['type'] ?? '',
            'brand' => $data['brand'] ?? '',
            'model' => $resolvedModel,
            'price_per_day' => $data['price'] ?? 0,
            'status' => $data['status'] ?? 'Available',
            'fuel_type' => $data['fuel'] ?? '',
            'transmission' => $data['transmission'] ?? '',
            'total_vehicles' => $data['total_vehicles'] ?? 1,
            'rider_details' => $data['rider_details'] ?? null,
            'insurance_fee' => $data['insurance_fee'] ?? null,
            'taxes_fee' => $data['taxes_fee'] ?? null,
            'description' => $data['description'] ?? '',
            'image_url' => $this->resolveSingleImageUrl($data, $photosData, '')
        ];

        if ($this->vehicleHasColumn('name')) {
            $vehicleData['name'] = $data['name'] ?? '';
        }

        if ($this->vehicleHasColumn('plate_number')) {
            $vehicleData['plate_number'] = $plateValue;
        } elseif ($this->vehicleHasColumn('plate')) {
            $vehicleData['plate'] = $plateValue;
        }

        if ($this->vehicleHasColumn('year')) {
            $vehicleData['year'] = (int) ($data['year'] ?? date('Y'));
        }

        if ($this->vehicleHasColumn('photos')) {
            $vehicleData['photos'] = json_encode(array_values($photosData));
        }

        Log::info('Vehicle data to create:', $vehicleData);

        $vehicleData = $this->filterVehicleDataBySchema($vehicleData);

        try {
            $record = Vehicle::create($vehicleData);
            Log::info('Vehicle created successfully with ID:', ['id' => $record->id]);
            return response()->json(new VehicleResource($record), 201);
        } catch (\Exception $e) {
            Log::error('Error creating vehicle:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to create vehicle. Please try again.'], 500);
        }
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('shop')->loadAvg('ratings', 'rating')->loadCount('ratings');
        return response()->json(new VehicleResource($vehicle));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        // Check if user is authorized to update this vehicle
        $user = $request->user();
        if ($user && $this->currentRole($request) !== 'admin') {
            // Get user's shop IDs
            $userShopIds = \App\Models\Shop::where('owner_id', $user->id)->pluck('id')->toArray();
            
            // Check if vehicle belongs to user's shop
            if (!in_array($vehicle->shop_id, $userShopIds)) {
                return response()->json([
                    'message' => 'Unauthorized. You can only update your own shop\'s vehicles.',
                ], 403);
            }
        }

        $request->merge($this->normalizeVehicleRequestData($request->all()));
        
        $request->validate([
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'plate' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:Available,Rented,Maintenance',
            'shop_id' => 'nullable|integer',
            'total_vehicles' => 'nullable|integer|min:0',
            'rider_details' => 'nullable|string|max:255',
            'insurance_fee' => 'nullable|numeric|min:0',
            'taxes_fee' => 'nullable|numeric|min:0',
        ]);
        
        $data = $request->all();
        
        // Handle photos - persist base64 as files and keep path references
        $photosData = $data['photos'] ?? null;
        if ($photosData !== null && is_array($photosData)) {
            $photosData = $this->normalizePhotos($photosData);
        } else {
            $photosData = json_decode($vehicle->photos ?? '[]', true);
        }
        
        $resolvedModel = $data['model'] ?? $vehicle->model;
        if (($resolvedModel === null || $resolvedModel === '') && !$this->vehicleHasColumn('name')) {
            $resolvedModel = $data['name'] ?? $vehicle->model;
        }

        $existingPlate = $vehicle->plate_number ?? ($vehicle->plate ?? '');
        $plateValue = $data['plate'] ?? $existingPlate;

        // Map frontend fields to database fields
        $vehicleData = [
            'shop_id' => $data['shop_id'] ?? $vehicle->shop_id,
            'type' => $data['category'] ?? $data['type'] ?? $vehicle->type,
            'brand' => $data['brand'] ?? $vehicle->brand,
            'model' => $resolvedModel,
            'price_per_day' => $data['price'] ?? $vehicle->price_per_day,
            'status' => $data['status'] ?? $vehicle->status,
            'fuel_type' => $data['fuel'] ?? $vehicle->fuel_type,
            'transmission' => $data['transmission'] ?? $vehicle->transmission,
            'total_vehicles' => $data['total_vehicles'] ?? $vehicle->total_vehicles ?? 1,
            'rider_details' => $data['rider_details'] ?? $vehicle->rider_details,
            'insurance_fee' => $data['insurance_fee'] ?? $vehicle->insurance_fee,
            'taxes_fee' => $data['taxes_fee'] ?? $vehicle->taxes_fee,
            'description' => $data['description'] ?? $vehicle->description,
            'image_url' => $this->resolveSingleImageUrl($data, $photosData, $vehicle->image_url)
        ];

        if ($this->vehicleHasColumn('name')) {
            $vehicleData['name'] = $data['name'] ?? $vehicle->name;
        }

        if ($this->vehicleHasColumn('plate_number')) {
            $vehicleData['plate_number'] = $plateValue;
        } elseif ($this->vehicleHasColumn('plate')) {
            $vehicleData['plate'] = $plateValue;
        }

        if ($this->vehicleHasColumn('year')) {
            $vehicleData['year'] = (int) ($data['year'] ?? $vehicle->year ?? date('Y'));
        }

        if ($this->vehicleHasColumn('photos')) {
            $vehicleData['photos'] = json_encode(array_values($photosData));
        }

        $vehicleData = $this->filterVehicleDataBySchema($vehicleData);

        try {
            $vehicle->update($vehicleData);
            return response()->json(new VehicleResource($vehicle->fresh()));
        } catch (\Exception $e) {
            Log::error('Error updating vehicle:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to update vehicle. Please try again.'], 500);
        }
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        // Check if user is authorized to delete this vehicle
        $user = $request->user();
        if ($user && $this->currentRole($request) !== 'admin') {
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
