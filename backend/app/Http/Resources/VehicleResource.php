<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $name = $this->name ?: ($this->model ?: '');
        $plateNumber = $this->plate_number ?: ($this->plate ?: '');

        // Build full image URL
        $imageUrl = null;
        if ($this->image_url) {
            $path = $this->image_url;
            if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
                $imageUrl = $path;
            } else {
                $cleanPath = ltrim(str_replace('\\', '/', (string) $path), '/');
                $imageUrl = asset('storage/' . $cleanPath);
            }
        }

        // Build full photo URLs
        $photoUrls = [];
        $photos = $this->photos;
        if (is_string($photos)) {
            $photos = json_decode($photos, true);
        }
        
        if (is_array($photos)) {
            foreach ($photos as $photo) {
                if (!$photo) continue;
                
                if (Str::startsWith($photo, ['http://', 'https://', 'data:'])) {
                    $photoUrls[] = $photo;
                } else {
                    $cleanPath = ltrim(str_replace('\\', '/', (string) $photo), '/');
                    $photoUrls[] = asset('storage/' . $cleanPath);
                }
            }
        }

        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'name' => $name,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'plate_number' => $plateNumber,
            'year' => $this->year,
            'price_per_day' => $this->price_per_day,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'total_vehicles' => $this->total_vehicles,
            'rider_details' => $this->rider_details,
            'insurance_fee' => $this->insurance_fee,
            'taxes_fee' => $this->taxes_fee,
            'status' => $this->status,
            'description' => $this->description,
            // Original image_url field
            'image_url' => $this->image_url,
            // Full image URL for display
            'image_url_full' => $imageUrl,
            // Original photos field
            'photos' => $photos ?? [],
            // Full photo URLs for display
            'photo_urls' => $photoUrls,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include shop relationship if loaded
            'shop' => $this->whenLoaded('shop'),
            'rating' => $this->ratings_count > 0 ? round($this->ratings_avg_rating, 1) : null,
            'rating_count' => (int) ($this->ratings_count ?? 0),
        ];
    }
}
