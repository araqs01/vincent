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
        $cacheKey = $this->cacheKey("categories-with-menu");

        $categories = $this->rememberCache($cacheKey, function () {
            return Category::query()
                ->whereNull('parent_id')
                ->with([
                    // 🔹 Подкатегории
                    'children' => fn($q) => $q->orderBy('id'),

                    // 🔹 Меню-блоки (только активные)
                    'menuBlocks' => fn($q) => $q
                        ->where('is_active', true)
                        ->orderBy('order_index')
                        ->with([
                            'values' => fn($v) => $v
                                ->where('is_active', true)
                                ->orderBy('order_index'),
                        ]),

                    // 🔹 Баннеры (только активные)
                    'menuBanners' => fn($q) => $q
                        ->where('is_active', true)
                        ->orderBy('order_index'),
                ])
                ->orderBy('id')
                ->get();
        }, 12 * 3600); // кэш 12 часов

        return $this->renderApi(
            resource: CategoryResource::collection($categories),
            additional: [
                'cached' => true,
                'locale' => app()->getLocale(),
            ]
        );
    }
}
