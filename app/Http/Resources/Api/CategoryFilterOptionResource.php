<?php


namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryFilterOptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'slug' => $this->slug,
            'meta' => $this->meta,
            'show_in_header' => (bool)$this->show_in_header,
            'order_index' => $this->order_index,
        ];
    }
}
