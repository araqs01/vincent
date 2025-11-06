<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'slug'          => $this->slug,
            'name'          => $this->name,
            'description'   => $this->description,
            'category'      => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'slug' => $this->category->slug,
                'name' => $this->category->name,
            ]),
            'brand'         => $this->whenLoaded('brand', fn () => [
                'id'   => $this->brand->id,
                'name' => $this->brand->name,
            ]),
            'brand_line'    => $this->whenLoaded('brandLine', fn () => [
                'id'   => $this->brandLine->id,
                'name' => $this->brandLine->name,
            ]),
            'region'        => $this->whenLoaded('region', fn () => [
                'id'   => $this->region->id,
                'name' => $this->region->name,
            ]),
            'supplier'      => $this->whenLoaded('supplier', fn () => [
                'id'   => $this->supplier->id,
                'name' => $this->supplier->name,
            ]),
            'manufacturer'  => $this->whenLoaded('manufacturer', fn () => [
                'id'   => $this->manufacturer->id,
                'name' => $this->manufacturer->name,
            ]),

            'price'         => $this->price,
            'final_price'   => $this->final_price,
            'has_discount'  => $this->hasDiscount(),
            'rating'        => $this->rating,
            'status'        => $this->status,

            // 🧃 Grape & variants
            'grapes'        => $this->whenLoaded('grapes', fn () =>
            $this->grapes->map(fn ($grape) => [
                'id'   => $grape->id,
                'name' => $grape->name,
                'percent' => $grape->pivot->percent ?? null,
                'main'    => $grape->pivot->main ?? false,
            ])
            ),

            'grape_variants' => $this->whenLoaded('grapeVariants', fn () =>
            $this->grapeVariants->map(fn ($variant) => [
                'id'   => $variant->id,
//                'name' => $variant->name,
                'percent' => $variant->pivot->percent ?? null,
                'main'    => $variant->pivot->main ?? false,
            ])
            ),

            // 🍷 Taste groups
            'tastes' => $this->whenLoaded('tastes', fn () =>
            $this->tastes->map(fn ($taste) => [
                'id'       => $taste->id,
                'name'     => $taste->name,
                'intensity_percent' => $taste->pivot->intensity_percent ?? null,
                'group'    => $taste->group?->name,
            ])
            ),

            // 🍽️ Pairings
            'pairings' => $this->whenLoaded('pairings', fn () =>
            $this->pairings->map(fn ($pairing) => [
                'id'   => $pairing->id,
                'name' => $pairing->name,
            ])
            ),

            // 🧩 Collections
            'collections' => $this->whenLoaded('collections', fn () =>
            $this->collections->map(fn ($collection) => [
                'id'   => $collection->id,
                'name' => $collection->name,
            ])
            ),

            // 🧴 Variants
            'variants' => $this->whenLoaded('variants', fn () =>
            $this->variants->map(fn ($variant) => [
                'id'           => $variant->id,
                'name'         => $variant->name,
                'volume'       => $variant->volume,
                'price'        => $variant->price,
                'final_price'  => $variant->final_price,
            ])
            ),

            // 🖼️ Image (from Spatie MediaLibrary)
            'image' => $this->getFirstMediaUrl('images') ?: null,

            // 🧠 Meta
            'meta' => $this->meta ?? [],
        ];
    }
}
