<?php


namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CategorySortOptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'title' => $this->title,
            'field' => $this->field,
            'direction' => $this->direction,
            'meta' => $this->meta,
            'ui_type' => $this->ui_type,
            'is_active' => $this->is_active,
            'order_index' => $this->order_index,
        ];
    }
}
