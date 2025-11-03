<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuBlockResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'order_index' => $this->order_index,
            'is_active' => (bool)$this->is_active,
            'values' => $this->whenLoaded('values', function () {
                return $this->values
                    ->sortBy('order_index')
                    ->map(fn($value) => [
                        'id' => $value->id,
                        'value' => $value->value,
                        'order_index' => $value->order_index,
                        'is_active' => (bool)$value->is_active,
                    ])
                    ->values();
            }),
        ];
    }
}
