<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuBlockValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'value'      => $this->value,
            'order'      => $this->order_index,
            'is_active'  => (bool) $this->is_active,
        ];
    }
}
