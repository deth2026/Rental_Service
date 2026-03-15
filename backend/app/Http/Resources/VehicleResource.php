<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'name' => $this->name ?? ($this->brand . ' ' . $this->model),
            'price_per_day' => $this->price_per_day,
            'price' => $this->price_per_day,
            'status' => $this->status,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'image_url_full' => $this->image_url_full,
            'photos' => $this->photos,
            'photo_urls' => $this->photo_urls,
            'year' => $this->year,
            'plate_number' => $this->plate_number ?? ($this->plate ?? null),
            'plate' => $this->plate ?? ($this->plate_number ?? null),
            'rating' => $this->rating ?? 4.8,
            'reviews' => $this->reviews ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
