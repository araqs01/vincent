<?php


namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryFilterResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'title' => $this->title,
            'mode' => $this->mode,
            'ui_type' => $this->ui_type,
            'config' => $this->config,
            'source_model' => $this->source_model,
            'is_active' => $this->is_active,
            'order_index' => $this->order_index,
            'values' => CategoryFilterOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
