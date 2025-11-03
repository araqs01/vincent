<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatedCollection extends ResourceCollection
{
    private $resourceClass;

    public function __construct($resource, $resourceClass)
    {
        parent::__construct($resource);
        /** @var LengthAwarePaginator resource */
        $this->resource = $this->collectResource($resource);
        $this->resourceClass = $resourceClass;
    }

    public function toArray($request)
    {
        $links = $this->resource->toArray();
        unset($links['data']);

        return array_merge(
            [
                'data' => $this->resourceClass::collection(
                    $this->collection
                )->resolve($request),
                'links' => $links,
            ],
            $this->additional
        );
    }
}
