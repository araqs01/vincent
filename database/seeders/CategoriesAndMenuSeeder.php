<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategorySortGroup;
use App\Models\CategorySortOption;
use App\Models\MenuBlock;
use App\Models\MenuBlockValue;
use App\Models\CategoryFilter;
use App\Models\CategoryFilterOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategoriesAndMenuSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/seeders/catalog/categories_with_menu.json');

        if (!File::exists($path)) {
            $this->command->error("❌ JSON file not found: {$path}");
            return;
        }

        $data = json_decode(File::get($path), true);
        $categories = collect($data['categories'] ?? []);

        $categories->each(function ($cat) {
            $ru = $cat['name']['ru'] ?? null;
            $en = $cat['name']['en'] ?? $ru;

            // 🟢 Категория
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($en ?? $ru)],
                [
                    'parent_id'   => $cat['parent_id'] ?? null,
                    'name'        => ['ru' => $ru, 'en' => $en],
                    'description' => null,
                    'type'        => null,
                ]
            );

            /** --------------------------------------------------------
             *  1️⃣  Меню-блоки
             * -------------------------------------------------------- */
            collect($cat['menu_blocks'] ?? [])->each(function ($block, $blockIndex) use ($category) {
                $blockRu = $block['title']['ru'] ?? null;
                $blockEn = $block['title']['en'] ?? $blockRu;

                $menuBlock = MenuBlock::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'type'        => $block['type'] ?? Str::slug($blockRu),
                    ],
                    [
                        'title'       => ['ru' => $blockRu, 'en' => $blockEn],
                        'order_index' => $blockIndex + 1,
                        'is_active'   => true,
                    ]
                );

                collect($block['values'] ?? [])->each(function ($val, $valIndex) use ($menuBlock) {
                    $valRu = $val['ru'] ?? null;
                    $valEn = $val['en'] ?? $valRu;

                    MenuBlockValue::updateOrCreate(
                        [
                            'menu_block_id' => $menuBlock->id,
                            'value->ru'     => $valRu,
                        ],
                        [
                            'value'       => ['ru' => $valRu, 'en' => $valEn],
                            'order_index' => $valIndex + 1,
                            'is_active'   => true,
                        ]
                    );
                });
            });


            collect($cat['menu_banners'] ?? [])->each(function ($banner, $bannerIndex) use ($category) {
                $bannerRu = $banner['title']['ru'] ?? null;
                $bannerEn = $banner['title']['en'] ?? $bannerRu;

                \App\Models\MenuBanner::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'filter_key'  => $banner['filter_key'] ?? Str::slug($bannerRu),
                    ],
                    [
                        'title'       => ['ru' => $bannerRu, 'en' => $bannerEn],
                        'is_active'   => $banner['active'] ?? true,
                        'order_index' => $banner['order'] ?? ($bannerIndex + 1),
                    ]
                );
            });

            /** --------------------------------------------------------
             *  2️⃣  Фильтры категории
             * -------------------------------------------------------- */
            collect($cat['filters'] ?? [])->each(function ($filter, $filterIndex) use ($category) {
                $filterRu = $filter['title']['ru'] ?? null;
                $filterEn = $filter['title']['en'] ?? $filterRu;

                $categoryFilter = CategoryFilter::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'key'         => $filter['key'] ?? Str::slug($filterRu),
                    ],
                    [
                        'title'        => ['ru' => $filterRu, 'en' => $filterEn],
                        'mode'         => $filter['mode'] ?? 'discrete',
                        'source_model' => $filter['source_model'] ?? null,
                        'config'       => $filter['config'] ?? null,
                        'order_index'  => $filterIndex + 1,
                        'is_active'    => true,
                    ]
                );

                collect($filter['values'] ?? [])->each(function ($val, $valIndex) use ($categoryFilter) {
                    $valRu = $val['ru'] ?? null;
                    $valEn = $val['en'] ?? $valRu;

                    CategoryFilterOption::updateOrCreate(
                        [
                            'filter_id' => $categoryFilter->id,
                            'value->ru' => $valRu,
                        ],
                        [
                            'value'       => ['ru' => $valRu, 'en' => $valEn],
                            'slug'        => Str::slug($valEn ?? $valRu),
                            'meta'        => $val['meta'] ?? null,
                            'order_index' => $valIndex + 1,
                            'is_active'   => true,
                            'show_in_header' => $val['show_in_header'] ?? false,
                        ]
                    );
                });
            });

            /** --------------------------------------------------------
             *  3️⃣  Сортировки категории
             * -------------------------------------------------------- */
            collect($cat['sorts'] ?? [])->each(function ($sortGroup, $groupIndex) use ($category) {
                $groupRu = $sortGroup['title']['ru'] ?? null;
                $groupEn = $sortGroup['title']['en'] ?? $groupRu;

                $sortGroupModel = CategorySortGroup::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'key'         => $sortGroup['key'] ?? Str::slug($groupRu),
                    ],
                    [
                        'title'       => ['ru' => $groupRu, 'en' => $groupEn],
                        'ui_type'     => $sortGroup['ui_type'] ?? 'dropdown',
                        'order_index' => $groupIndex + 1,
                        'is_active'   => true,
                    ]
                );

                collect($sortGroup['options'] ?? [])->each(function ($opt, $optIndex) use ($sortGroupModel) {
                    $optRu = $opt['title']['ru'] ?? null;
                    $optEn = $opt['title']['en'] ?? $optRu;

                    CategorySortOption::updateOrCreate(
                        [
                            'group_id' => $sortGroupModel->id,
                            'key'      => $opt['key'] ?? Str::slug($optRu),
                        ],
                        [
                            'title'      => ['ru' => $optRu, 'en' => $optEn],
                            'field'      => $opt['field'] ?? null,
                            'type'       => $opt['type'] ?? 'scale',
                            'direction'  => $opt['direction'] ?? 'asc',
                            'is_active'  => true,
                            'order_index'=> $optIndex + 1,
                            'ui_type'    => $opt['ui_type'] ?? 'dropdown',
                            'meta'       => [
                                'scale_labels' => $opt['scale_labels'] ?? null,
                                'value' => $opt['value'] ?? null
                            ],
                        ]
                    );
                });
            });
        });

        $this->command->info('✅ Categories, Menu Blocks, Filters, and Sorts imported successfully!');
    }
}
