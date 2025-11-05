<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\CategoryFilterResource;
use App\Http\Resources\Api\CategorySortGroupResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function catalog(Request $request)
    {
        $cacheKey = $this->cacheKey("categories-with-menu");

        $categories = $this->rememberCache($cacheKey, function () {
            return Category::query()
                ->whereNull('parent_id')
                ->whereHas('menuBlocks', function ($q) {
                    $q->where('is_active', true);
                })
                ->with([
                    'children' => fn($q) => $q->orderBy('id'),
                    'menuBlocks' => fn($q) => $q
                        ->where('is_active', true)
                        ->orderBy('order_index')
                        ->with([
                            'values' => fn($v) => $v
                                ->where('is_active', true)
                                ->orderBy('order_index'),
                        ]),
                    'menuBanners' => fn($q) => $q
                        ->where('is_active', true)
                        ->orderBy('order_index'),
                ])
                ->orderBy('id')
                ->get();
        }, 12 * 3600);

        return $this->renderApi(
            resource: CategoryResource::collection($categories),
            additional: [
                'cached' => true,
                'locale' => app()->getLocale(),
            ]
        );
    }

    public function index(Request $request)
    {
        $cacheKey = $this->cacheKey("categories-index-" . app()->getLocale());

        $categories = $this->rememberCache($cacheKey, function () {
            return Category::query()
                ->select(['id', 'parent_id', 'slug', 'name', 'description'])
                ->whereNull('parent_id')
                ->orderBy('id')
                ->get();
        }, 6 * 3600);

        return $this->renderApi(
            resource: CategoryResource::collection($categories),
            additional: [
                'cached' => true,
                'count'  => $categories->count(),
                'locale' => app()->getLocale(),
            ]
        );
    }

    /**
     * GET /api/categories/filters?slug=wine
     */
    public function filters(Request $request, string $slug)
    {
        $cacheKey = $this->cacheKey("category-{$slug}-filters-" . app()->getLocale());

        $filters = $this->rememberCache($cacheKey, function () use ($slug) {
            $category = Category::where('slug', $slug)->firstOrFail();

            return $category->filters()
                ->active()
                ->with([
                    'options' => fn($q) => $q
                        ->where('is_active', true)
                        ->orderBy('order_index'),
                ])
                ->orderBy('order_index')
                ->get();
        }, 6 * 3600);

        return $this->renderApi(
            resource: CategoryFilterResource::collection($filters),
            additional: [
                'category_slug' => $slug,
                'cached' => true,
            ]
        );
    }


    /**
     * GET /api/categories/sorts?slug=wine
     */
    public function sorts(string $slug)
    {
        $cacheKey = $this->cacheKey("category-{$slug}-sorts-" . app()->getLocale());

        $sortGroups = $this->rememberCache($cacheKey, function () use ($slug) {
            $category = Category::where('slug', $slug)->first();

            if (!$category) {
                return collect(); // не выбрасываем 404, просто пустой результат
            }

            return $category->sortGroups()
                ->where('is_active', true)
                ->with([
                    'options' => fn($q) => $q
                        ->where('is_active', true)
                        ->orderBy('order_index'),
                ])
                ->orderBy('order_index')
                ->get();
        }, 6 * 3600);

        return $this->renderApi(
            resource: CategorySortGroupResource::collection($sortGroups),
            additional: [
                'category_slug' => $slug,
                'cached' => true,
            ]
        );
    }

}
