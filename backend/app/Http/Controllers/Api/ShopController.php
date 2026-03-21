<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with(['owner:id,name,email,phone'])
            ->orderByDesc('id')
            ->get();

        return response()->json($shops);
    }

    public function store(Request $request)
    {
        // Validate most fields first; img_url is handled separately because it
        // can be either a file upload (multipart) or a plain string URL.
        $payload = $request->validate([
            'city_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_reviews' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
            // do NOT validate img_url here; see below
        ]);

        if (!$this->shopColumnExists('location')) {
            unset($payload['location']);
        }
        
        // handle image validation / payload separately
        if ($request->hasFile('img_url')) {
            // file rule ensures it is an image and not too large
            $request->validate(['img_url' => 'image|max:10240']);
        } elseif ($request->filled('img_url')) {
            // if it's provided as a string we still want to make sure it's not
            // some non-string type (e.g. array) that would break the model
            $request->validate(['img_url' => 'string']);
        }

        // The creator becomes the owner by default.
        $payload['owner_id'] = $request->user()?->id;

        // Handle image upload or URL
        if ($request->hasFile('img_url')) {
            $image = $request->file('img_url');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/shops', $imageName);
            $payload['img_url'] = 'shops/' . $imageName;
        } elseif ($request->input('img_url')) {
            // Accept base64 data URL or regular URL
            $imgUrl = $request->input('img_url');
            // If it's a base64 data URL, decode and save it
            if (preg_match('/^data:image\/(\w+);base64,/', $imgUrl, $matches)) {
                $extension = $matches[1];
                $imageName = time() . '_' . uniqid() . '.' . $extension;
                $imageData = base64_decode(substr($imgUrl, strpos($imgUrl, ',') + 1));
                $path = storage_path('app/public/shops/' . $imageName);
                
                // Ensure directory exists
                if (!file_exists(storage_path('app/public/shops'))) {
                    mkdir(storage_path('app/public/shops'), 0755, true);
                }
                
                file_put_contents($path, $imageData);
                $payload['img_url'] = 'shops/' . $imageName;
            }
            // If it's a regular URL, store it as-is
            elseif (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                $payload['img_url'] = $imgUrl;
            }
        }

        // If no coordinates provided, attempt to geocode from address
        if (!isset($payload['latitude']) && !isset($payload['longitude'])) {
            [$lat, $lng] = $this->geocodeAddress($payload['address'] ?? null);
            if ($lat && $lng) {
                $payload['latitude'] = $lat;
                $payload['longitude'] = $lng;
            }
        }

        $record = Shop::create($payload);
        try {
            NotificationService::shopCreated($record);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send admin notification for new shop', [
                'error' => $exception->getMessage(),
                'shop_id' => $record->id,
            ]);
        }

        return response()->json($record, 201);
    }

    public function show(Shop $shop)
    {
        return response()->json($shop->load('owner'));
    }

    public function update(Request $request, Shop $shop)
    {
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? $user->user_type ?? ''));
        $isAdmin = $role === 'admin';
        if ($user && !$isAdmin && (int) $shop->owner_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only update your own shops.',
            ], 403);
        }

        // As with store(), we validate everything except img_url up front
        $payload = $request->validate([
            'city_id' => 'nullable|integer',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_reviews' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if (!$this->shopColumnExists('location')) {
            unset($payload['location']);
        }

        // validate img_url value type depending on upload or text
        if ($request->hasFile('img_url')) {
            $request->validate(['img_url' => 'image|max:10240']);
        } elseif ($request->filled('img_url')) {
            $request->validate(['img_url' => 'string']);
        }

        $shouldRemoveImage = $request->boolean('remove_img');

        // Handle image upload or URL
        if ($shouldRemoveImage) {
            if ($shop->img_url && !filter_var($shop->img_url, FILTER_VALIDATE_URL)) {
                $oldPath = storage_path('app/public/' . $shop->img_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $payload['img_url'] = null;
        } elseif ($request->hasFile('img_url')) {
            // Delete old image if exists
            if ($shop->img_url && !filter_var($shop->img_url, FILTER_VALIDATE_URL)) {
                $oldPath = storage_path('app/public/' . $shop->img_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $image = $request->file('img_url');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/shops', $imageName);
            $payload['img_url'] = 'shops/' . $imageName;
        } elseif ($request->input('img_url')) {
            // Accept base64 data URL or regular URL
            $imgUrl = $request->input('img_url');
            // If it's a base64 data URL, decode and save it
            if (preg_match('/^data:image\/(\w+);base64,/', $imgUrl, $matches)) {
                // Delete old image if exists
                if ($shop->img_url && !filter_var($shop->img_url, FILTER_VALIDATE_URL)) {
                    $oldPath = storage_path('app/public/' . $shop->img_url);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                
                $extension = $matches[1];
                $imageName = time() . '_' . uniqid() . '.' . $extension;
                $imageData = base64_decode(substr($imgUrl, strpos($imgUrl, ',') + 1));
                $path = storage_path('app/public/shops/' . $imageName);
                
                // Ensure directory exists
                if (!file_exists(storage_path('app/public/shops'))) {
                    mkdir(storage_path('app/public/shops'), 0755, true);
                }
                
                file_put_contents($path, $imageData);
                $payload['img_url'] = 'shops/' . $imageName;
            }
            // If it's a regular URL, store it as-is
            elseif (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                $payload['img_url'] = $imgUrl;
            }
        } else {
            // Remove img_url from payload if not uploading new image
            unset($payload['img_url']);
        }

        // If latitude/longitude not provided, refresh from the (possibly updated) address
        $addressForGeocode = $payload['address'] ?? $shop->address;
        $coordsMissing = !$request->filled('latitude') && !$request->filled('longitude');
        if ($coordsMissing && $addressForGeocode) {
            [$lat, $lng] = $this->geocodeAddress($addressForGeocode);
            if ($lat && $lng) {
                $payload['latitude'] = $lat;
                $payload['longitude'] = $lng;
            }
        }

        $shop->update($payload);

        return response()->json($shop->fresh());
    }

    public function destroy(Request $request, Shop $shop)
    {
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? $user->user_type ?? ''));
        $isAdmin = $role === 'admin';
        if ($user && !$isAdmin && (int) $shop->owner_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only delete your own shops.',
            ], 403);
        }

        if ($shop->img_url && !filter_var($shop->img_url, FILTER_VALIDATE_URL)) {
            $oldPath = storage_path('app/public/' . ltrim($shop->img_url, '/'));
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $shop->delete();

        return response()->json(['message' => 'Shop deleted successfully']);
    }

    /**
     * Check whether the shops table contains the given column.
     */
    private function shopColumnExists(string $column): bool
    {
        static $cache = [];
        if (!array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('shops', $column);
        }
        return $cache[$column];
    }

    /**
     * Resolve an address to latitude/longitude using Google Geocoding API.
     * Returns [lat, lng] or [null, null] on failure.
     */
    private function geocodeAddress(?string $address): array
    {
        if (!$address) {
            return [null, null];
        }

        $apiKey = config('services.google_maps.key') ?? env('GEOCODING_API_KEY');
        if (!$apiKey) {
            return [null, null];
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $apiKey,
        ]);

        if (!$response->successful()) {
            return [null, null];
        }

        $result = $response->json('results.0.geometry.location');
        if (!is_array($result) || !isset($result['lat'], $result['lng'])) {
            return [null, null];
        }

        return [$result['lat'], $result['lng']];
    }
}
