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
        $category = Category::where('slug', $slug)->first();

        return $this->renderApi(
            resource: $this->renderCollectionResponse(
                $request,
                Product::query()
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
                        'grapeVariants:id,grape_id',
                        'grapeVariants.grape:id,name',
                        'tastes' => fn($q) => $q->with('group:id,name'),
                        'pairings:id,name',
                        'collections:id,name',
                        'variants' => fn($q) => $q
                            ->select('id', 'product_id', 'volume', 'price', 'final_price', 'sku', 'barcode', 'stock', 'vintage'),
                        'media',
                    ])
                    ->orderBy('id'),
                ProductResource::class
            ),
        );
    }
}
