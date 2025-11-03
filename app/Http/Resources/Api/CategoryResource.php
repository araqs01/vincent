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
            'id' => $this->id,
            'slug' => $this->slug,
            'type' => $this->type,
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'children' => $this->whenLoaded('children', fn() =>
            CategoryResource::collection($this->children)
            ),
        ];
    }
}
