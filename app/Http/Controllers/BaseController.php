<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginatedCollection;
use App\Models\user;
use App\Traits\CacheableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseRouteController;
use Illuminate\Support\Collection;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

abstract class BaseController extends BaseRouteController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    use CacheableTrait;

    public function __construct(Request $request)
    {
        $this->setLanguage($request);
    }

    public function renderApi(
        JsonResource|array $resource = null,
        string $message = null,
        array $additional = [],
        int $status = ResponseAlias::HTTP_OK): JsonResponse
    {
        $request = request();
        if ($resource instanceof JsonResource) {
            $resource = $resource->resolve($request);
        }

        if (is_array($resource)) {
            if (! array_key_exists('data', $resource)) {
                $resource = ['data' => $resource];
            }
        }

        if ($additional) {
            $resource = array_merge($resource ?? [], ['additional' => $additional]);
        }

        if (! empty($message)) {
            $resource = array_merge($resource ?? [], ['message' => $message]);
        }

        $response = collect([])->when(
            ! empty($resource),
            fn (Collection $collection) => $collection->merge($resource)
        );

        if ($response->isNotEmpty()) {
            $response = $response->sortByDesc(function ($item) {
                return is_array($item);
            })->toArray();
        }

        $response = collect($response)->sortBy(fn ($item, $key) => $key === 'message' ? -1 : 1)->toArray();

        return response()->json($response, $status);
    }

    /**
     * @param  int  $httpCode
     */
    public function render(JsonResource|array $resource = null, array $additional = [], $httpCode = 200): mixed
    {
        $request = request();
        if ($resource instanceof JsonResource) {
            $resource = $resource->resolve($request);
        }

        if (is_array($resource)) {
            if (! array_key_exists('data', $resource)) {
                $resource = ['data' => $resource];
            }
            if ($additional) {
                $resource = array_merge($resource, $additional);
            }
        }

        $response = collect([])->when(
            ! empty($resource),
            fn (Collection $collection) => $collection->merge($resource)
        )->when(
            empty($resource) && $additional,
            fn (Collection $collection) => $collection->merge($additional)
        );

        if ($response->isNotEmpty()) {
            $response = $response->sortByDesc(function ($item) {
                return is_array($item);
            })->toArray();
        }

        if ($request->has('ajax')) {
            return $response;
        }

        return response($response, $httpCode);
    }

    public function renderCollectionResponse(
        Request $request,
        Builder|Relation $data,
        JsonResource|string $resource,
        int $defaultItemPerPage = 10,
        array $additional = []
    ): array|JsonSerializable|Arrayable {
        return $request->has('all')
            ? $resource::collection($data->get())->additional($additional)->toArray($request)
            : (new PaginatedCollection(
                $data
                    ->paginate($request->get('per_page', $defaultItemPerPage))
                    ->withQueryString(),
                $resource
            ))->additional($additional)->toArray($request);
    }

    public function getUser(): Authenticatable|user|null
    {
        return auth()->user();
    }

    private function setLanguage(Request $request)
    {
        if ($request->lang == 'hy') {
            app()->setLocale($request->lang);
        }
        if ($request->lang == 'en') {
            app()->setLocale($request->lang);
        }
    }
}
