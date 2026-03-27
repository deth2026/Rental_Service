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
            ->paginate(25);

        // Transform paginated collection
        $shopsWithImages = $shops->getCollection()->map(function ($shop) {
            return [
                'id' => $shop->id,
                'owner_id' => $shop->owner_id,
                'city_id' => $shop->city_id,
                'name' => $shop->name,
                'description' => $shop->description,
                'address' => $shop->address,
                'location' => $shop->location,
                'phone' => $shop->phone,
                'img_url' => $shop->img_url,
                'img_url_full' => $shop->img_url_full,
                'image' => $shop->img_url_full,
                'latitude' => $shop->latitude,
                'longitude' => $shop->longitude,
                'map_url' => $shop->map_url,
                'total_reviews' => $shop->total_reviews,
                'status' => $shop->status,
                'created_at' => $shop->created_at,
                'updated_at' => $shop->updated_at,
                'owner' => $shop->owner
            ];
        });

        $shops->setCollection($shopsWithImages);

        return response()->json($shops);
    }

    public function store(Request $request)
    {
        // Validate most fields first; img_url is handled separately because it
        // can be either a file upload (multipart) or a plain string URL.
        $payload = $request->validate([
            'owner_id' => 'nullable|integer|exists:users,id',
            'city_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'location' => 'nullable|string|max:2048',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'map_url' => 'nullable|string|max:2048',
            'total_reviews' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
            // do NOT validate img_url here; see below
        ]);

        if (!$this->shopColumnExists('location')) {
            unset($payload['location']);
        }
        if (!$this->shopColumnExists('map_url')) {
            unset($payload['map_url']);
        }

        $mapUrlValue = trim((string) ($request->input('map_url') ?? ($payload['map_url'] ?? '')));
        $locationValue = trim((string) ($payload['location'] ?? ''));
        if ($this->shopColumnExists('location')) {
            $payload['location'] = $this->buildLocationValue(
                $locationValue,
                $mapUrlValue,
                $payload['address'] ?? null
            );
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

        // Admin may assign shop ownership explicitly; others default to self.
        $role = strtolower((string) ($request->user()?->role ?? $request->user()?->user_type ?? ''));
        if ($role === 'admin' && isset($payload['owner_id']) && (int) $payload['owner_id'] > 0) {
            $payload['owner_id'] = (int) $payload['owner_id'];
        } else {
            $payload['owner_id'] = $request->user()?->id;
        }

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

        [$urlLat, $urlLng] = $this->extractCoordinatesFromMapUrl($payload['map_url'] ?? null);
        if (!isset($payload['latitude']) && $urlLat !== null) {
            $payload['latitude'] = $urlLat;
        }
        if (!isset($payload['longitude']) && $urlLng !== null) {
            $payload['longitude'] = $urlLng;
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
            'location' => 'nullable|string|max:2048',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'map_url' => 'nullable|string|max:2048',
            'total_reviews' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        if (!$this->shopColumnExists('location')) {
            unset($payload['location']);
        }
        if (!$this->shopColumnExists('map_url')) {
            unset($payload['map_url']);
        }

        $mapUrlValue = trim((string) ($request->input('map_url') ?? ($payload['map_url'] ?? $shop->map_url ?? '')));
        $locationValue = trim((string) ($payload['location'] ?? $shop->location ?? ''));
        if ($this->shopColumnExists('location')) {
            $payload['location'] = $this->buildLocationValue(
                $locationValue,
                $mapUrlValue,
                $payload['address'] ?? $shop->address ?? null
            );
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

        $mapUrlForCoords = array_key_exists('map_url', $payload)
            ? $payload['map_url']
            : $shop->map_url;
        [$urlLat, $urlLng] = $this->extractCoordinatesFromMapUrl($mapUrlForCoords);
        if (!$request->filled('latitude') && $urlLat !== null) {
            $payload['latitude'] = $urlLat;
        }
        if (!$request->filled('longitude') && $urlLng !== null) {
            $payload['longitude'] = $urlLng;
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
     * Build a location value that is short enough for the DB column and usable for navigation.
     * Priority: map URL -> explicit location -> address.
     */
    private function buildLocationValue($locationValue, $mapUrlValue, $addressValue): ?string
    {
        $location = trim((string) $locationValue);
        $mapUrl = trim((string) $mapUrlValue);
        $address = trim((string) $addressValue);

        if ($mapUrl !== '') {
            return $this->compactLocationLink($mapUrl, $address);
        }
        if ($location !== '') {
            return $this->compactLocationLink($location, $address);
        }
        if ($address !== '') {
            return mb_substr($address, 0, 255);
        }

        return null;
    }

    private function compactLocationLink(string $value, string $fallbackAddress = ''): string
    {
        $input = trim($value);
        [$lat, $lng] = $this->extractCoordinatesFromMapUrl($input);
        if ($lat !== null && $lng !== null) {
            return 'https://www.google.com/maps?q='
                . $this->formatCoordinate($lat)
                . ','
                . $this->formatCoordinate($lng);
        }

        if (mb_strlen($input) <= 255) {
            return $input;
        }

        $address = trim($fallbackAddress);
        if ($address !== '') {
            $searchLink = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
            if (mb_strlen($searchLink) <= 255) {
                return $searchLink;
            }
            return mb_substr($searchLink, 0, 255);
        }

        return mb_substr($input, 0, 255);
    }

    private function formatCoordinate(float $value): string
    {
        $formatted = number_format($value, 7, '.', '');
        $trimmed = rtrim(rtrim($formatted, '0'), '.');
        return $trimmed === '-0' ? '0' : $trimmed;
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

    /**
     * Extract coordinates from common Google Maps URL patterns.
     * Returns [lat, lng] or [null, null] when no coordinates are found.
     */
    private function extractCoordinatesFromMapUrl(?string $value): array
    {
        $url = trim((string) $value);
        if ($url === '') {
            return [null, null];
        }

        $patterns = [
            '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
            '/[?&](?:q|query|ll|destination|origin)=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
            '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',
        ];

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $url, $matches)) {
                continue;
            }

            $lat = isset($matches[1]) ? (float) $matches[1] : null;
            $lng = isset($matches[2]) ? (float) $matches[2] : null;
            if ($lat === null || $lng === null) {
                continue;
            }
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                continue;
            }

            return [$lat, $lng];
        }

        return [null, null];
    }
}
