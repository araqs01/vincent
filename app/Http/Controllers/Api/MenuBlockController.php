<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\MenuBlockResource;
use App\Models\Category;
use App\Models\MenuBlock;
use Illuminate\Http\Request;

class MenuBlockController extends BaseController
{
    public function index(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $cacheKey = $this->cacheKey('menu-blocks', $slug);

        $blocks = $this->rememberCache($cacheKey, function () use ($category) {
            return MenuBlock::query()
                ->where('category_id', $category->id)
                ->where('is_active', true)
                ->with([
                    'values' => fn($q) => $q
                        ->where('is_active', true)
                        ->orderBy('order_index'),
                ])
                ->orderBy('order_index')
                ->get();
        }, 6 * 3600);

        return $this->renderApi(
            resource: MenuBlockResource::collection($blocks),
            additional: [
                'category' => $category->name,
                'locale' => app()->getLocale(),
                'cached' => true,
            ]
        );
    }
}
