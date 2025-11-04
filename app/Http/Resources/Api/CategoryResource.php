<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'type'        => $this->type,
            'name'        => $this->name,
            'menu_blocks' => $this->whenLoaded('menuBlocks', fn() =>
            MenuBlockResource::collection($this->menuBlocks->sortBy('order_index'))
            ),

            // 🔹 Баннеры меню
            'menu_banners' => $this->whenLoaded('menuBanners', fn() =>
            MenuBannerResource::collection($this->menuBanners->sortBy('order'))
            ),
        ];
    }
}
