<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        $cacheKey = $this->cacheKey('categories-tree');

        $categories = $this->rememberCache($cacheKey, function () {
            return Category::query()
                ->whereNull('parent_id')
                ->with(['children' => fn($q) => $q->orderBy('id')])
                ->orderBy('id')
                ->get();
        }, 24 * 3600); // кеш 24 часа

        return $this->renderApi(
            resource: CategoryResource::collection($categories),
            additional: [
                'cached' => true,
                'locale' => app()->getLocale(),
            ]
        );
    }
}
