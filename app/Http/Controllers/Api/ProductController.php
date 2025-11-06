<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function index(Request $request, ?string $slug = null)
    {
        $slug = $slug ?? $request->query('category');

        // 🟢 Находим категорию по slug
        $category = Category::where('slug', $slug)->first();
//
//        if (!$category) {
//            return $this->renderApi([], ['error' => 'Category not found'], 404);
//        }

        // 🟢 Загружаем продукты со всеми нужными связями
        $products = Product::query()
            ->where('category_id', $category->id)
            ->where('status', 'active')
            ->with([
                'category:id,slug,name',
                'brand:id,name',
                'brandLine:id,name',
                'region:id,name',
                'supplier:id,name',
                'manufacturer:id,name',

                'grapes:id,name',

                // ✅ Группы виноградных вариантов
                'grapeVariants:id,grape_id',
                'grapeVariants.grape:id,name',

                // ✅ Вкусы с группами
                'tastes' => fn($q) => $q->with('group:id,name'),

                // ✅ Гастрономические сочетания
                'pairings:id,name',

                // ✅ Коллекции
                'collections:id,name',

                // ✅ Варианты продукта — без поля is_active и name
                'variants' => fn($q) => $q
                    ->select('id', 'product_id', 'volume', 'price', 'final_price', 'sku', 'barcode', 'stock', 'vintage'),

                // ✅ Медиа (Spatie)
                'media',
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        // 🟢 Возвращаем красиво оформленный ответ
        return $this->renderApi(
            resource: ProductResource::collection($products),
            additional: [
                'cached' => false,
                'category_slug' => $slug,
                'locale' => app()->getLocale(),
            ]
        );
    }
}
