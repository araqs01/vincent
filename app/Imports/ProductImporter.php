<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductAttributeService;
use App\Services\ProductGrapeService;
use App\Services\ProductGrapeVariantService;
use App\Services\ProductPairingService;
use App\Services\ProductTasteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductImporter implements ToCollection, WithChunkReading, WithBatchInserts
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function collection(Collection $rows)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $headerIndex = $this->detectHeaderRow($rows);
        if ($headerIndex === null) return;

        $headers = collect($rows[$headerIndex])
            ->map(fn($v) => is_string($v) ? trim(mb_strtolower($v)) : $v)
            ->filter(fn($v) => $v !== null && $v !== '')
            ->values()
            ->toArray();

        $dataRows = $rows->slice($headerIndex + 1)->filter(
            fn($row) => $row->filter(fn($v) => $v !== null && trim((string)$v) !== '')->isNotEmpty()
        );

        DB::transaction(function () use ($headers, $dataRows) {
            foreach ($dataRows as $index => $row) {
                try {
                    $assoc = $this->combineRowWithHeaders($headers, $row->toArray());
                    $normalized = $this->normalizeRow($assoc);
                    if (empty($normalized)) continue;

                    // 🔹 Основные поля из характеристик
                    if (!empty($normalized['ws_characteristics'])) {
                        try {
                            $chars = $this->safeJsonDecode($normalized['ws_characteristics']) ?? [];
                            if (is_array($chars)) {
                                $metaFromChars = [];

                                foreach ($chars as $char) {
                                    if (!is_array($char)) continue;
                                    $key = trim(mb_strtolower($char['key'] ?? ''));
                                    $val = trim((string)($char['values'] ?? ''));
                                    if ($key === '' || $val === '') continue;

                                    switch ($key) {
                                        case 'страна':
                                            $normalized['страна'] = $val;
                                            break;
                                        case 'регион':
                                            $normalized['регион'] = $val;
                                            break;
                                        case 'бренд':
                                            $normalized['бренд'] = $val;
                                            break;
                                        case 'сорта винограда':
                                        case 'виноград':
                                        case 'grape':
                                        case 'grapes':
                                            $normalized['grapes'] = trim(($normalized['grapes'] ?? '') . ', ' . $val, ', ');
                                            break;
                                        case 'подходит к':
                                        case 'гастрономические сочетания':
                                            $normalized['pairing'] = trim(($normalized['pairing'] ?? '') . ', ' . $val, ', ');
                                            break;
                                        // ⚠️ Больше НЕ парсим "вкус", "характер", "тело", "кислотность"
                                        // эти данные идут из Vivino или сортов винограда
                                        case 'крепость':
                                        case 'температура сервировки':
                                            $metaFromChars[ucfirst($key)] = $val;
                                            break;
                                    }
                                }

                                if (!empty($metaFromChars)) {
                                    $normalized['meta_from_chars'] = $metaFromChars;
                                }
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Ошибка обработки ws_characteristics: ' . $e->getMessage());
                        }
                    }

                    // 🔹 Описание товара (ws_about_product)
                    $metaSections = [];
                    if (!empty($normalized['ws_about_product'])) {
                        $aboutSections = $this->safeJsonDecode($normalized['ws_about_product']);
                        if (is_array($aboutSections)) {
                            foreach ($aboutSections as $section) {
                                if (!is_array($section)) continue;
                                $title = trim($section['title'] ?? '');
                                $text = trim($section['text'] ?? '');
                                if ($title && $text) {
                                    $metaSections[] = ['title' => $title, 'text' => $text];
                                }
                            }
                        }
                    }

                    // 🔹 Очистка и нормализация строк
                    foreach (['wine_tastes', 'pairing'] as $field) {
                        if (!empty($normalized[$field])) {
                            $normalized[$field] = trim($normalized[$field], ", \t\n\r\0\x0B");
                        }
                    }

                    // 🔹 Основные поля продукта
                    $nameRu = $normalized['name_price'] ?? $normalized['name_ru'] ?? null;
                    $nameEn = $normalized['name_price_en'] ?? null;
                    $nameWithYear = $normalized['name_price_year'] ?? $nameRu;
                    [$baseName, $volume, $vintage] = $this->parseNameVolumeAndVintage($nameWithYear);
                    $slug = Str::slug(Str::limit($baseName, 80, ''));
                    $descriptionRu = trim(($normalized['about'] ?? '') . "\n\n" . ($normalized['description'] ?? ''));
                    $descriptionEn = $normalized['description_en'] ?? null;
                    $price = $this->sanitizePrice($normalized['price'] ?? null);
                    $alcoholStrength = $this->parseFloat($normalized['alcohol_strength'] ?? null);

                    $category = $this->detectCategory($normalized);
                    $regionId = $this->detectOrCreateRegion(
                        $normalized['страна'] ?? $normalized['country'] ?? null,
                        $normalized['регион'] ?? $normalized['region'] ?? null
                    );
                    $brandId = $this->detectOrCreateNameModel(\App\Models\Brand::class, $normalized['бренд'] ?? null, $regionId);
                    $manufacturerId = $this->detectOrCreateNameModel(\App\Models\Manufacturer::class, $normalized['manufacturer'] ?? null, $regionId);


                    $product = Product::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => ['ru' => $baseName, 'en' => $nameEn ?: $baseName],
                            'description' => ['ru' => $descriptionRu, 'en' => $descriptionEn ?: ''],
                            'category_id' => $category?->id,
                            'brand_id' => $brandId,
                            'manufacturer_id' => $manufacturerId,
                            'region_id' => $regionId,
                            'status' => 'active',
                            'price' => $price,
                            'final_price' => $price,
                            'alcohol_strength' => $alcoholStrength,
                        ]
                    );

                    // 🔹 Цвет и тип (атрибуты)
                    ProductAttributeService::extractAndAttachAttributes($product, $baseName);

                    // 🔹 Объём / винтаж
                    if ($volume || $vintage) {
                        ProductVariant::updateOrCreate(
                            ['product_id' => $product->id, 'volume' => $volume, 'vintage' => $vintage],
                            ['price' => $price, 'final_price' => $price]
                        );
                    }

                    // 🍇 Привязка сортов и вариантов винограда
                    if (!empty($normalized['grapes'])) {
                        ProductGrapeService::attachGrapes($product, (string)$normalized['grapes']);
                        ProductGrapeVariantService::updateGrapeProfile($product);

                        // 🧩 Добавляем вкусы от винограда (из grape_variant_taste)
                        $variantIds = $product->grapeVariants()
                            ->select('grape_variants.id as gv_id')
                            ->distinct()
                            ->pluck('gv_id');
                        if ($variantIds->isNotEmpty()) {
                            $grapeTastes = \App\Models\Taste::query()
                                ->whereIn('id', function ($q) use ($variantIds) {
                                    $q->select('taste_id')
                                        ->from('grape_variant_taste')
                                        ->whereIn('grape_variant_id', $variantIds);
                                })
                                ->get();

                            if ($grapeTastes->isNotEmpty()) {
                                $sync = [];
                                $total = $grapeTastes->count();
                                $oddStep = 1 / $total;   // шаг для нечетных
                                $evenStep = 0.5 / $total; // базовый шаг для четных (0.5, 0.4, ...)

                                $oddValue = 1.0;
                                $evenValue = 0.5;

                                foreach ($grapeTastes->values() as $i => $taste) {
                                    $x = $i + 1;

                                    if ($x % 2 !== 0) {
                                        // нечетные (1,3,5…)
                                        $val = max(0, $oddValue);
                                        $oddValue -= $oddStep;
                                    } else {
                                        // четные (2,4,6…)
                                        $val = max(0, $evenValue);
                                        $evenValue -= $evenStep;
                                    }

                                    $sync[$taste->id] = ['intensity_percent' => round($val * 100)];
                                }

                                // 🔹 Привязываем вкусы к продукту
                                $product->tastes()->sync($sync);

                                // 🔹 Получаем вкусы с их группами
                                $tastes = $product->tastes()
                                    ->select('tastes.id', 'tastes.taste_group_id', 'tastes.name', 'product_taste.intensity_percent')
                                    ->with(['group:id,slug,name'])
                                    ->get()
                                    ->filter(fn($t) => $t->group);


                                // ==============================
                                // 🧩 Построение taste_groups (по группам вкусов)
                                // ==============================
                                if ($tastes->isNotEmpty()) {
                                    $grouped = $tastes->groupBy(fn($t) => $t->group->slug);

                                    // Среднее значение интенсивности в каждой группе
                                    $avgByGroup = $grouped->mapWithKeys(function ($items, $slug) {
                                        $avg = round($items->avg(fn($t) => $t->pivot->intensity_percent ?? 0), 1);
                                        $name = json_decode($items->first()->group->name, true)['ru'] ?? $slug;
                                        return [$name => $avg];
                                    });

                                    // Нормализация: лидирующая группа = 100%
                                    $max = max($avgByGroup->values()->toArray());
                                    $normalizedGroups = $avgByGroup->map(fn($v) => round(($v / $max) * 100, 1));

                                    $tasteScaleMap = [
                                        'Фруктовость' => ['fruits', 'red-berries', 'tropical-fruits', 'citrus'],
                                        'Сладость' => ['sweets'],
                                        'Полнотелость' => ['woody', 'toasted/smoky', 'nutty', 'spices'],
                                        'Танинность' => ['woody', 'spices'],
                                        'Кислотность' => ['minerals/stone/elements', 'herbs'],
                                    ];

                                    $scaleValues = [];

                                    foreach ($tasteScaleMap as $scale => $relatedSlugs) {
                                        $matchedGroups = $grouped->filter(fn($_, $slug) => in_array($slug, $relatedSlugs));
                                        if ($matchedGroups->isNotEmpty()) {
                                            $avg = $matchedGroups->flatten()
                                                ->avg(fn($t) => $t->pivot->intensity_percent ?? 0);
                                            $scaleValues[$scale] = round($avg);
                                        } else {
                                            $scaleValues[$scale] = 0; // ✅ если нет данных, ставим 0
                                        }
                                    }
                                    $maxScale = max($scaleValues) ?: 1; // чтобы не делить на 0
                                    foreach ($scaleValues as $k => $v) {
                                        $scaleValues[$k] = round(($v / $maxScale) * 100, 1);
                                    }
                                    $defaultScales = [
                                        'Фруктовость' => 0,
                                        'Сладость' => 0,
                                        'Полнотелость' => 0,
                                        'Танинность' => 0,
                                        'Кислотность' => 0,
                                    ];

                                    $scaleValues = array_merge($defaultScales, $scaleValues);

                                    // ==============================
                                    // 💾 Сохраняем всё в meta
                                    // ==============================
                                    $meta = $product->meta ?? [];
                                    $meta['taste_groups'] = $normalizedGroups->toArray();
                                    $meta['taste_scales'] = $scaleValues;
                                    $product->meta = $meta;
                                    $product->save();
                                }
                            }
                        }
                    }

                    // 🍷 Построение вкусового профиля (из Vivino/текста)
                    ProductTasteService::buildAndAttachTastes(
                        product: $product,
                        textTastesCsv: $normalized['wine_tastes'] ?? null,
                        descriptionRu: $descriptionRu,
                        hasOakByFilters: false
                    );

                    // 📦 Meta (sections, ratings)
                    $currentMeta = $product->meta ?? [];

                    if (!empty($metaSections)) {
                        $currentMeta['sections'] = $metaSections;
                    }
                    if (!empty($normalized['vivino_rating'])) {
                        $currentMeta['vivino_rating'] = (float)$normalized['vivino_rating'];
                    }
                    if (!empty($normalized['manufacturer_rating'])) {
                        $currentMeta['manufacturer_rating'] = (float)$normalized['manufacturer_rating'];
                    }


                    $product->meta = $currentMeta;
                    $product->save();
                    // 🖼 Загрузка изображения
                    $imageUrl = $normalized['image_link'] ?? $normalized['foto'] ?? null;
                    if ($imageUrl && $imageUrl !== 'https://s2.wine.style/images_gen/116/11675/0_0_695x600.webp') {
                        $filename = basename(parse_url($imageUrl, PHP_URL_PATH)) ?: 'image.jpg';
                        if (!$product->getMedia('images')->contains(fn($m) => $m->file_name === $filename)) {
                            try {
                                $response = Http::get($imageUrl);
                                if ($response->successful()) {
                                    $product->addMediaFromString($response->body())
                                        ->usingFileName($filename)
                                        ->toMediaCollection('images');
                                }
                            } catch (\Throwable $e) {
                                Log::warning("Ошибка загрузки изображения для продукта ID {$product->id}: {$e->getMessage()}");
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("💥 Ошибка при обработке строки {$index}: " . $e->getMessage());
                }
            }
        });
    }

    public function chunkSize(): int { return 1000; }
    public function batchSize(): int { return 300; }
    public $tries = 3;
    public $timeout = 900;

    protected function detectHeaderRow(Collection $rows): ?int
    {
        $maxTextCount = 0;
        $likelyHeaderIndex = null;

        foreach ($rows as $index => $row) {
            $textCount = collect($row)
                ->filter(fn($v) => is_string($v) && mb_strlen(trim($v)) > 2)
                ->count();

            if ($textCount > $maxTextCount) {
                $maxTextCount = $textCount;
                $likelyHeaderIndex = $index;
            }
        }

        return $likelyHeaderIndex ?? 1;
    }

    protected function combineRowWithHeaders(array $headers, array $values): array
    {
        $assoc = [];
        foreach ($headers as $i => $header) {
            if (!$header) continue;
            $value = $values[$i] ?? null;
            if ($value === null || trim((string)$value) === '') continue;
            $assoc[$header] = trim((string)$value);
        }
        return $assoc;
    }

    protected function normalizeRow(array $row): array
    {
        $map = [
            'name_price' => 'name_price',
            'name_price_year' => 'name_price_year',
            'name_ru' => 'name_ru',
            'ws_name_ru' => 'ws_name_ru',
            'описание' => 'description',
            'ws_description' => 'description',
            'about' => 'about',
            'цена' => 'price',
            'ws_price' => 'price',
            'vivino_link' => 'vivino_link',
            'wine_tastes' => 'wine_tastes',
            'бренд' => 'бренд',
            'производитель' => 'manufacturer',
            'manufacturer' => 'manufacturer',
            'регион' => 'регион',
            'страна' => 'страна',
            'foto' => 'foto',
            'image_link' => 'image_link',
            'vivino_rating' => 'vivino_rating',
            'manufacturer_rating' => 'manufacturer_rating',
            'винтаж' => 'vintage',
            'vintage' => 'vintage',
            'подходит к' => 'pairing',
            'гастрономические сочетания' => 'pairing',
            'pairing' => 'pairing',
            'pairings' => 'pairing',
            'гастр. сочетания' => 'pairing',
            'free_remainder' => 'free_remainder',
            'ws_characteristics' => 'ws_characteristics',
            'ws_about_product' => 'ws_about_product',
            'сорта винограда' => 'grapes',
            'виноград' => 'grapes',
            'grape' => 'grapes',
            'grapes' => 'grapes',
            'wine_type' => 'wine_type',
            'тип' => 'wine_type',
            'крепость' => 'alcohol_strength',
            'alcohol_strength' => 'alcohol_strength',
            'крепость (%)' => 'alcohol_strength'
        ];

        $normalized = [];
        foreach ($row as $key => $value) {
            $keyLower = trim(mb_strtolower($key));
            $target = $map[$keyLower] ?? $keyLower;
            $normalized[$target] = is_string($value) ? trim($value) : $value;
        }

        return array_filter($normalized, fn($v) => $v !== null && $v !== '');
    }

    protected function sanitizePrice($value): float
    {
        if (empty($value)) return 0.0;
        $clean = preg_replace('/[^0-9.,]/', '', (string)$value);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float)$clean : 0.0;
    }

    protected function parseNameVolumeAndVintage(string|array|null $name): array
    {
        if (empty($name)) {
            return ['', null, null];
        }

        if (is_array($name)) {
            $name = reset($name);
        }

        $name = trim((string)$name);
        $name = preg_replace('/[\x{00A0}\x{202F}]/u', ' ', $name); // убираем неразрывные пробелы

        $base = $name;
        $year = null;
        $volume = null;

        // 🎯 1️⃣ Ищем год (1900–текущий)
        if (preg_match('/\b(19|20)\d{2}\b/u', $name, $m)) {
            $year = $m[0];
            $base = trim(str_replace($m[0], '', $base));
        }

        // 🎯 2️⃣ Ищем объём (0.75л, 0,2 л, 750ml, 500 мл)
        if (preg_match('/(\d{1,4}[.,]?\d{0,3})\s*(л|ml|мл)\b/iu', $name, $m)) {
            $volume = str_replace(',', '.', $m[1]);
            $base = trim(str_replace($m[0], '', $base));
        }

        // 🧹 3️⃣ Убираем лишние пробелы, точки и запятые
        $base = preg_replace('/\s{2,}/', ' ', $base);
        $base = trim($base, " \t\n\r\0\x0B.,-");

        return [$base, $volume, $year];
    }


    protected function detectOrCreateNameModel(string $model, ?string $name, ?int $regionId = null): ?int
    {
        if (empty($name)) {
            return null;
        }

        // 🧹 Очистка имени — убираем года и слово "год"
        $cleanedName = trim(preg_replace('/\b(19|20)\d{2}\b|\bгод\b/iu', '', $name));
        $cleanedName = preg_replace('/\s{2,}/u', ' ', $cleanedName); // убираем двойные пробелы

        // 🔎 Ищем по очищенному названию
        $item = $model::firstOrNew(['name->ru' => $cleanedName]);
        $item->fill(['name' => ['ru' => $cleanedName, 'en' => $cleanedName]]);

        if (empty($item->region_id)) {
            $item->region_id = $regionId;
        }

        $item->save();

        return $item->id;
    }

    protected function detectCategory(array $normalized): ?Category
    {
        $categoryMap = [
            // Вино
            'вино' => 'ВИНО',
            'вермут' => 'ВИНО',
            'вин санто' => 'ВИНО',
            'глинтвейн' => 'ВИНО',
            'десертное' => 'ВИНО',
            'кагор' => 'ВИНО',
            'кошерное' => 'ВИНО',
            'крепленное' => 'ВИНО',
            'ликерное' => 'ВИНО',
            'мадера' => 'ВИНО',
            'марсала' => 'ВИНО',
            'портвейн' => 'ВИНО',
            'сотерн' => 'ВИНО',
            'херес' => 'ВИНО',
            'шерри' => 'ВИНО',

            // Шампанское
            'шампанское' => 'ШАМПАНСКОЕ',
            'игристое' => 'ШАМПАНСКОЕ',
            'брют' => 'ШАМПАНСКОЕ',

            // Виски
            'виски' => 'ВИСКИ',
            'бурбон' => 'ВИСКИ',
            'скотч' => 'ВИСКИ',

            // Коньяк
            'коньяк' => 'КОНЬЯК',
            'арманьяк' => 'КОНЬЯК',
            'бренди' => 'КОНЬЯК',
            'кальвадос' => 'КОНЬЯК',

            // Крепкие напитки
            'абсент' => 'КРЕПКИЕ НАПИТКИ',
            'аквавит' => 'КРЕПКИЕ НАПИТКИ',
            'аперитив' => 'КРЕПКИЕ НАПИТКИ',
            'граппа' => 'КРЕПКИЕ НАПИТКИ',
            'джин' => 'КРЕПКИЕ НАПИТКИ',
            'кашаса' => 'КРЕПКИЕ НАПИТКИ',
            'ликер' => 'КРЕПКИЕ НАПИТКИ',
            'мескаль' => 'КРЕПКИЕ НАПИТКИ',
            'настойка' => 'КРЕПКИЕ НАПИТКИ',
            'писко' => 'КРЕПКИЕ НАПИТКИ',
            'ракия' => 'КРЕПКИЕ НАПИТКИ',
            'ром' => 'КРЕПКИЕ НАПИТКИ',
            'самогон' => 'КРЕПКИЕ НАПИТКИ',
            'текила' => 'КРЕПКИЕ НАПИТКИ',
            'узо' => 'КРЕПКИЕ НАПИТКИ',
            'сакэ' => 'КРЕПКИЕ НАПИТКИ',
            'соджу' => 'КРЕПКИЕ НАПИТКИ',

            // Водка
            'водка' => 'ВОДКА',
            'чача' => 'ВОДКА',
            'шнапс' => 'ВОДКА',

            // Пиво
            'пиво' => 'ПИВО И СИДР',
            'сидр' => 'ПИВО И СИДР',
            'эйл' => 'ПИВО И СИДР',
            'лагер' => 'ПИВО И СИДР',
            'стаут' => 'ПИВО И СИДР',
            'портер' => 'ПИВО И СИДР',

            // Вода
            'вода' => 'ВОДА И ЛИМОНАДЫ',
            'тоник' => 'ВОДА И ЛИМОНАДЫ',
            'сироп' => 'ВОДА И ЛИМОНАДЫ',
            'сок' => 'ВОДА И ЛИМОНАДЫ',
            'нектар' => 'ВОДА И ЛИМОНАДЫ',
            'морс' => 'ВОДА И ЛИМОНАДЫ',

            // Продукты
            'масло' => 'ПРОДУКТЫ',
            'уксус' => 'ПРОДУКТЫ',
            'печенье' => 'ПРОДУКТЫ',
            'шоколад' => 'ПРОДУКТЫ',
            'сладости' => 'ПРОДУКТЫ',
            'макароны' => 'ПРОДУКТЫ',
            'соус' => 'ПРОДУКТЫ',
            'консервация' => 'ПРОДУКТЫ',

            // Аксессуары
            'бокал' => 'АКСЕССУАРЫ',
            'графин' => 'АКСЕССУАРЫ',
            'декантер' => 'АКСЕССУАРЫ',
            'пробка' => 'АКСЕССУАРЫ',
            'штопор' => 'АКСЕССУАРЫ',
            'стакан' => 'АКСЕССУАРЫ',

            // Табак
            'табак' => 'ТАБАК & ВЭЙП',
            'сигара' => 'ТАБАК & ВЭЙП',
            'вейп' => 'ТАБАК & ВЭЙП',
            'кальян' => 'ТАБАК & ВЭЙП',
            'жидкость' => 'ТАБАК & ВЭЙП',
        ];

        $rawType = $normalized['wine_type'] ?? $normalized['тип'] ?? null;
        $normalizedName = mb_strtolower(trim((string)$rawType));

        $categoryName = null;

        // 1️⃣ Если тип явно указан в таблице соответствий — используем его
        foreach ($categoryMap as $keyword => $cat) {
            if (str_contains($normalizedName, $keyword)) {
                $categoryName = $cat;
                break;
            }
        }

        // 2️⃣ Если не нашли в карте — используем сам wine_type как категорию
        if (!$categoryName && $normalizedName) {
            $categoryName = Str::upper($normalizedName);
        }

        // 3️⃣ Если вообще ничего — категория "ПРОЧЕЕ"
        if (!$categoryName) {
            $categoryName = 'ПРОЧЕЕ';
        }

        // 4️⃣ Ищем или создаём категорию в БД
        $existing = Category::whereRaw(
            'LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru"))) = ?',
            [mb_strtolower($categoryName)]
        )->first();

        if ($existing) return $existing;

        return Category::create([
            'name' => ['ru' => $categoryName, 'en' => Str::title(Str::lower($categoryName))],
            'slug' => Str::slug($categoryName),
            'type' => 'default',
            'description' => ['ru' => '', 'en' => ''],
        ]);
    }


    protected function detectOrCreateRegion(?string $country, ?string $region): ?int
    {
        if (!$country && $region && str_contains($region, ',')) {
            [$countryPart, $regionPart] = array_map('trim', explode(',', $region, 2));
            $country = $countryPart;
            $region = $regionPart;
        }

        if (empty($country) && empty($region)) {
            return null;
        }

        $country = $country ? trim($country) : null;
        $region = $region ? trim($region) : null;

        // 🔹 Утилита для нормализации текста (без регистра, дефисов, ё/й)
        $normalize = fn($v) => trim(mb_strtolower(
            str_replace(['ё', 'й', '’', "'", '"', '–', '—', '-', '  '], ['е', 'и', '', '', '', ' ', ' ', ' ', ' '], $v)
        ));

        // 🚫 1. Проверка: регион с таким названием уже есть (в любом регистре)
        if ($region) {
            $normalizedRegion = $normalize($region);

            $existingRegion = \App\Models\Region::get()->first(function ($r) use ($normalize, $normalizedRegion) {
                $nameRu = is_array($r->name)
                    ? ($r->name['ru'] ?? null)
                    : optional(json_decode($r->name, true))['ru'] ?? $r->name;
                return $normalize($nameRu) === $normalizedRegion;
            });

            if ($existingRegion) {
                return $existingRegion->id;
            }
        }

        // 🧭 2. Загружаем все корневые регионы (страны и крупные области)
        $rootRegions = \App\Models\Region::whereNull('parent_id')->get();

        // 📍 3. Пытаемся определить родителя по началу строки
        $parentRegion = null;
        foreach ($rootRegions as $root) {
            $rootName = is_array($root->name)
                ? ($root->name['ru'] ?? $root->name['en'])
                : optional(json_decode($root->name, true))['ru'] ?? $root->name;

            if ($rootName && str_starts_with($normalize($region), $normalize($rootName) . ' ')) {
                $parentRegion = $root;
                $region = trim(Str::after($region, $root->getTranslation('name', 'ru')));
                break;
            }
        }

        // 🚫 4. Проверка на составные регионы (Пьемонт Асти, Тоскана Кьянти Классико, Россия Краснодарский край)
        if ($region && !$parentRegion && str_contains($region, ' ')) {
            [$maybeParent, $maybeChild] = array_map('trim', explode(' ', $region, 2));

            // Ищем родителя
            $parent = \App\Models\Region::whereNull('parent_id')
                ->get()
                ->first(fn($r) => $normalize($r->getTranslation('name', 'ru')) === $normalize($maybeParent));

            // Если нашли родителя — проверяем, есть ли у него дочерний с таким именем
            if ($parent) {
                $child = \App\Models\Region::where('parent_id', $parent->id)->get()
                    ->first(fn($r) => $normalize($r->getTranslation('name', 'ru')) === $normalize($maybeChild));

                if ($child) {
                    // 💡 Уже есть parent + child — просто возвращаем ID ребёнка
                    return $child->id;
                }
            }
        }

        // 🏳️ 5. Создаём / находим страну (верхний уровень)
        $countryRegion = null;
        if ($country) {
            $countryRegion = \App\Models\Region::whereNull('parent_id')->get()
                ->first(fn($r) => $normalize($r->getTranslation('name', 'ru')) === $normalize($country));

            if (!$countryRegion) {
                $countryRegion = \App\Models\Region::create([
                    'name' => ['ru' => $country, 'en' => $country],
                    'parent_id' => null,
                ]);
            }
        }

        // 🏞️ 6. Если нашли родителя — ищем или создаём дочерний
        if ($parentRegion && $region) {
            $existingChild = \App\Models\Region::where('parent_id', $parentRegion->id)->get()
                ->first(fn($r) => $normalize($r->getTranslation('name', 'ru')) === $normalize($region));

            if ($existingChild) {
                return $existingChild->id;
            }

            return \App\Models\Region::create([
                'name' => ['ru' => ucfirst($region), 'en' => ucfirst($region)],
                'parent_id' => $parentRegion->id,
            ])->id;
        }

        // 🧩 7. Если нет родителя, ищем под страной
        if ($region) {
            $existing = \App\Models\Region::where('parent_id', $countryRegion?->id)->get()
                ->first(fn($r) => $normalize($r->getTranslation('name', 'ru')) === $normalize($region));

            if ($existing) {
                return $existing->id;
            }

            return \App\Models\Region::create([
                'name' => ['ru' => ucfirst($region), 'en' => ucfirst($region)],
                'parent_id' => $countryRegion?->id,
            ])->id;
        }

        return $countryRegion?->id;
    }

    protected function safeJsonDecode($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = (string)$value;

        // 🔹 Удаляем невидимые и неразрешённые символы (в т.ч. soft-hyphen \xAD и неразрывные пробелы)
        $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
        $value = preg_replace('/[\x00-\x1F\x7F\xA0\xAD]/u', '', $value);

        // 🔹 Принудительно приводим к UTF-8
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        }

        // 🔹 Нормализация типографики
        $value = str_replace(
            ['“', '”', '„', '‟', '«', '»', '‘', '’', '‹', '›'],
            '"',
            $value
        );

        // 🔹 Исправляем python-формат
        if (preg_match('/^\s*\[\s*\{\'/', $value) || preg_match('/^\s*\{\s*\'/', $value)) {
            $value = str_replace("'", '"', $value);
        }

        // 🔹 Убираем запятые перед закрытием
        $value = preg_replace('/,\s*([\]}])/m', '$1', $value);

        // 🔹 Исправляем d"Oro / Harat"s / d"Avola
        $value = preg_replace('/([A-Za-zА-Яа-яЁё])\"([A-Za-zА-Яа-яЁё])/', "$1'$2", $value);

        // 🔹 Исправляем вложенные кавычки внутри текстов ("Спритц", "Аньолотти", "Cola Royal")
        $value = preg_replace_callback(
            '/\"text\"\s*:\s*\"(.*?)\"(\s*[},])/su',
            function ($m) {
                $txt = $m[1];
                $txt = preg_replace('/(?<!\\\\)\"/u', '«', $txt);
                $txt = preg_replace('/«([^\«]*)$/u', '«$1»', $txt);
                return '"text": "' . $txt . '"' . $m[2];
            },
            $value
        );

        // 🔹 Подчищаем BOM, пробелы
        $value = trim($value, "\xEF\xBB\xBF\t\n\r ");

        // 🔹 Основная попытка декодирования
        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {

            // 🧩 Попытка 2 — при ошибке кодировки
            if (str_contains($e->getMessage(), 'Malformed UTF-8')) {
                $value = mb_convert_encoding($value, 'UTF-8', 'auto');
                try {
                    return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e2) {
                    \Log::warning('Ошибка JSON decode после перекодировки: ' . $e2->getMessage(), [
                        'value' => Str::limit($value, 700),
                    ]);
                }
            }

            // 🧩 Попытка 3 — заменяем все двойные кавычки внутри текста на «»
            $fallback = preg_replace_callback(
                '/\"text\"\s*:\s*\"(.*?)\"(\s*[},])/su',
                fn($m) => '"text": "' . str_replace('"', '«', $m[1]) . '"' . $m[2],
                $value
            );

            try {
                return json_decode($fallback, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e3) {

                // 🧩 Попытка 4 — фиксим оборванные фразы вроде «Спритц»", в сочетании...
                if (str_contains($e3->getMessage(), 'Syntax error')) {
                    $fixed = preg_replace('/»\"\s*,\s*в/u', '», в', $value);
                    $fixed = preg_replace('/\"\,\s*в\s+/u', ', в ', $fixed);
                    try {
                        return json_decode($fixed, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\JsonException $e4) {
                        \Log::warning('Ошибка JSON decode (после фикса кавычки перед запятой): ' . $e4->getMessage(), [
                            'value' => Str::limit($value, 700),
                        ]);
                    }
                }

                // ❌ Всё ещё неудачно — логируем финал
                \Log::warning('Ошибка JSON decode (финально): ' . $e3->getMessage(), [
                    'value' => Str::limit($value, 700),
                ]);
                return null;
            }
        }
    }

    protected function parseFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // 🟢 Excel DateTime
        if ($value instanceof \DateTimeInterface) {
            $day = (int)$value->format('d');
            $month = (int)$value->format('m');
            return round($day + $month / 10, 1);
        }

        $value = trim((string)$value);

        // 🟡 Английские месяцы ("12.May")
        if (preg_match('/^(\d{1,2})[-.\s]?(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/i', $value, $m)) {
            $monthMap = [
                'jan' => 0.1, 'feb' => 0.2, 'mar' => 0.3, 'apr' => 0.4,
                'may' => 0.5, 'jun' => 0.6, 'jul' => 0.7, 'aug' => 0.8,
                'sep' => 0.9, 'oct' => 1.0, 'nov' => 1.1, 'dec' => 1.2,
            ];
            $base = (float)$m[1];
            $suffix = $monthMap[strtolower($m[2])] ?? 0;
            return round($base + $suffix, 1);
        }

        // 🔵 Русские месяцы ("12.май")
        if (preg_match('/^(\d{1,2})[.\s]?(янв|фев|мар|апр|май|июн|июл|авг|сен|окт|ноя|дек)/ui', $value, $m)) {
            $monthMap = [
                'янв' => 0.1, 'фев' => 0.2, 'мар' => 0.3, 'апр' => 0.4,
                'май' => 0.5, 'июн' => 0.6, 'июл' => 0.7, 'авг' => 0.8,
                'сен' => 0.9, 'окт' => 1.0, 'ноя' => 1.1, 'дек' => 1.2,
            ];
            $base = (float)$m[1];
            $suffix = $monthMap[mb_strtolower($m[2])] ?? 0;
            return round($base + $suffix, 1);
        }

        // ⚪ Формат "12.05.2025"
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $m)) {
            $day = (int)$m[1];
            $month = (int)$m[2];
            return round($day + $month / 10, 1);
        }

        // 🟠 Excel numeric date
        if (is_numeric($value) && $value > 40000) {
            $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            $day = (int)$dt->format('d');
            $month = (int)$dt->format('m');
            return round($day + $month / 10, 1);
        }

        // ⚫ Обычное число
        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? (float)$value : null;
    }


}
