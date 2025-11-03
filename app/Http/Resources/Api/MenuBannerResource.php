<?php


namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuBannerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'filter_key' => $this->filter_key,
            'order' => $this->order_index,
            'is_active' => (bool)$this->is_active,
            'image' => $this->getFirstMediaUrl('image') ?? $this->image ?? null,
        ];
    }
}
