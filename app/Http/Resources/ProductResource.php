<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => CategoryResource::make($this->category)),
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discounted_price' => (float) $this->discounted_price ?: null,
            'sale_price' => $this->sale_price,
            'is_favorited' => $this->when(isset($this->is_favorited), fn () => $this->is_favorited, $this->when(isset($this->favorites), fn () => $this->favorites->isNotEmpty())),
        ];
    }
}
