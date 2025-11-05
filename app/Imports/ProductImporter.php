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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductImporter implements ToCollection, WithChunkReading
{
    public function collection(Collection $rows)
    {
        ini_set('max_execution_time', 900);
        set_time_limit(900);

        $headerIndex = $this->detectHeaderRow($rows);
        if ($headerIndex === null) {
            return;
        }

        $headers = collect($rows[$headerIndex])
            ->map(fn($v) => is_string($v) ? trim(mb_strtolower($v)) : $v)
            ->filter(fn($v) => $v !== null && $v !== '')
            ->values()
            ->toArray();

        $dataRows = $rows->slice($headerIndex + 1)->filter(function ($row) {
            return $row->filter(fn($v) => $v !== null && trim((string)$v) !== '')->isNotEmpty();
        });

        DB::transaction(function () use ($headers, $dataRows) {
            foreach ($dataRows as $row) {
                $assoc = $this->combineRowWithHeaders($headers, $row->toArray());
                $normalized = $this->normalizeRow($assoc);
                if (empty($normalized)) continue;

                if (!empty($normalized['ws_characteristics'])) {
                    try {
                        $chars = $normalized['ws_characteristics'];

                        if (is_string($chars)) {
                            if (str_contains($chars, "{'")) {
                                $chars = preg_replace_callback(
                                    '/\'(.*?)\'/',
                                    fn($m) => '"' . str_replace('"', '\"', $m[1]) . '"',
                                    $chars
                                );
                            }
                            $chars = json_decode($chars, true);
                        }

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

                                    case 'производитель':
                                        $normalized['производитель'] = $val;
                                        break;

                                    case 'сорта винограда':
                                    case 'виноград':
                                        $normalized['grapes'] = trim(($normalized['grapes'] ?? '') . ', ' . $val, ', ');
                                        break;

                                    case 'подходит к':
                                    case 'гастрономические сочетания':
                                        $normalized['pairing'] = trim(($normalized['pairing'] ?? '') . ', ' . $val, ', ');
                                        break;

                                    case 'аромат':
                                    case 'характер':
                                    case 'тело':
                                    case 'кислотность':
                                        $normalized['wine_tastes'] = trim(($normalized['wine_tastes'] ?? '') . ', ' . $val, ', ');
                                        break;

                                    // 🎯 Новые ключи для meta
                                    case 'крепость':
                                    case 'насыщенность':
                                    case 'глубина цвета':
                                    case 'температура сервировки':
                                        $metaFromChars[ucfirst($key)] = $val;
                                        break;
                                }
                            }

                            // 💾 сохраняем в meta
                            if (!empty($metaFromChars)) {
                                $normalized['meta_from_chars'] = $metaFromChars;
                            }
                        } else {
                            \Log::warning('ws_characteristics не распознан как JSON: ' . substr((string)$normalized['ws_characteristics'], 0, 120));
                        }
                    } catch (\Throwable $e) {
                        \Log::warning('Ошибка JSON-декодирования ws_characteristics: ' . $e->getMessage());
                    }
                }



                /*
                |--------------------------------------------------------------------------
                | ws_about_product
                |--------------------------------------------------------------------------
                */
                $metaSections = [];
                if (!empty($normalized['ws_about_product'])) {
                    try {
                        $value = $normalized['ws_about_product'];

                        if (is_string($value)) {
                            $fixed = trim($value);

                            if (str_starts_with($fixed, '[') && str_contains($fixed, "'")) {
                                $fixed = str_replace("'", '"', $fixed);
                            }

                            $aboutSections = json_decode($fixed, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                \Log::warning('Ошибка JSON ws_about_product: ' . json_last_error_msg(), ['value' => $value]);
                                $aboutSections = null;
                            }
                        } else {
                            $aboutSections = is_array($value) ? $value : null;
                        }

                        // Формируем только meta.sections
                        if (is_array($aboutSections)) {
                            foreach ($aboutSections as $section) {
                                if (!is_array($section)) continue;

                                $title = trim($section['title'] ?? '');
                                $text  = trim($section['text'] ?? '');

                                if ($title && $text) {
                                    $metaSections[] = [
                                        'title' => $title,
                                        'text'  => $text,
                                    ];
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::warning('Ошибка обработки ws_about_product: ' . $e->getMessage());
                    }
                }


//                dump($metaSections);
                // Очистка лишних запятых
                foreach (['wine_tastes', 'pairing'] as $field) {
                    if (!empty($normalized[$field])) {
                        $normalized[$field] = trim($normalized[$field], ", \t\n\r\0\x0B");
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Нормализация строковых полей
                |--------------------------------------------------------------------------
                */
                if (!empty($normalized['grapes'])) {
                    $normalized['grapes'] = collect(
                        preg_split('/[,;\/]+|\s{2,}|\s(?=[А-ЯЁA-Z][а-яё]{2,}\s[А-ЯЁA-Z])/u', $normalized['grapes'])
                    )->map(fn($v) => trim($v))
                        ->filter()
                        ->unique()
                        ->implode(', ');
                }

                if (!empty($normalized['pairing'])) {
                    $normalized['pairing'] = collect(
                        preg_split('/[,;\/]+|\s{2,}/u', $normalized['pairing'])
                    )->map(fn($v) => trim($v))
                        ->filter()
                        ->unique()
                        ->implode(', ');
                }

                /*
                |--------------------------------------------------------------------------
                | Создание/обновление продукта
                |--------------------------------------------------------------------------
                */
                $nameRu = $normalized['name_price'] ?? $normalized['name_ru'] ?? null;
                $nameEn = $normalized['name_price_en'] ?? null;
                $nameWithYear = $normalized['name_price_year'] ?? $nameRu;

                [$baseName, $volume, $vintage] = $this->parseNameVolumeAndVintage($nameWithYear);
                $baseName = trim(preg_replace('/\s{2,}/', ' ', preg_replace('/[\/\\\()\[\]\d.,]+$/u', '', $baseName)));
                $slug = Str::slug(Str::limit($baseName, 80, ''));

                $descriptionRu = trim(($normalized['about'] ?? '') . "\n\n" . ($normalized['description'] ?? ''));
                $descriptionEn = $normalized['description_en'] ?? null;
                $price = $this->sanitizePrice($normalized['price'] ?? null);

                $category = $this->detectCategoryFromName($baseName);
                $regionId = $this->detectOrCreateRegion(
                    $normalized['страна'] ?? $normalized['country'] ?? null,
                    $normalized['регион'] ?? $normalized['region'] ?? null
                );
                $brandId = $this->detectOrCreateNameModel(\App\Models\Brand::class, $normalized['бренд'] ?? null, $regionId);
                $manufacturerId = $this->detectOrCreateNameModel(\App\Models\Manufacturer::class, $normalized['производитель'] ?? null, $regionId);

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
                    ]
                );

                ProductAttributeService::extractAndAttachAttributes($product, $baseName);

                if ($volume || $vintage) {
                    ProductVariant::updateOrCreate(
                        ['product_id' => $product->id, 'volume' => $volume, 'vintage' => $vintage],
                        ['price' => $price, 'final_price' => $price]
                    );
                }

                if (!empty($normalized['grapes'])) {
                    ProductGrapeService::attachGrapes($product, (string)$normalized['grapes']);
                    ProductGrapeVariantService::updateGrapeProfile($product);
                }

                ProductTasteService::buildAndAttachTastes(
                    product: $product,
                    textTastesCsv: $normalized['wine_tastes'] ?? null,
                    descriptionRu: $descriptionRu,
                    hasOakByFilters: false
                );

                if (!empty($normalized['pairing'])) {
                    ProductPairingService::attachPairings($product, $normalized['pairing']);
                }

                /*
                |--------------------------------------------------------------------------
                | Финальное объединение meta (sections + taste_groups + rating)
                |--------------------------------------------------------------------------
                */
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

                // 🔹 Изображения
                $imageUrl = $normalized['image_link'] ?? $normalized['foto'] ?? null;
                if ($product && $imageUrl) {
                    $filename = basename(parse_url($imageUrl, PHP_URL_PATH)) ?: 'image.jpg';
                    $alreadyExists = $product->getMedia('images')->contains(fn($m) => $m->file_name === $filename);
                    if (!$alreadyExists) {
                        try {
                            $response = Http::get($imageUrl);
                            if ($response->successful()) {
                                $product->addMediaFromString($response->body())
                                    ->usingFileName($filename)
                                    ->toMediaCollection('images');
                            }
                        } catch (\Throwable $e) {
                            \Log::warning("Ошибка загрузки изображения для продукта ID {$product->id}: {$e->getMessage()}");
                        }
                    }
                }
            }
        });
    }

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
            'производитель' => 'производитель',
            'регион' => 'регион',
            'страна' => 'страна',
            'foto' => 'foto',
            'image_link' => 'image_link',
            'vivino_rating' => 'vivino_rating',
            'manufacturer_rating' => 'manufacturer_rating',
            'сорта винограда' => 'grapes',
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
        ];

        $normalized = [];
        foreach ($row as $key => $value) {
            $keyLower = trim(mb_strtolower($key));
            $target = $map[$keyLower] ?? $keyLower;
            $normalized[$target] = is_string($value) ? trim($value) : $value;
        }

        // 🔍 Обработка JSON-характеристик
        if (!empty($normalized['ws_characteristics'])) {
            $chars = json_decode($normalized['ws_characteristics'], true);
            if (is_array($chars)) {
                foreach ($chars as $char) {
                    $key = trim($char['key'] ?? '');
                    $val = trim($char['values'] ?? '');
                    if (!$key || !$val) continue;

                    switch (mb_strtolower($key)) {
                        case 'крепость':
                            $normalized['strength'] = $val;
                            break;
                        case 'объем':
                            $normalized['volume'] = $val;
                            break;
                        case 'бренд':
                            $normalized['бренд'] = $val;
                            break;
                        case 'регион':
                            $normalized['регион'] = $val;
                            break;
                        case 'страна':
                            $normalized['страна'] = $val;
                            break;
                        case 'производитель':
                            $normalized['производитель'] = $val;
                            break;
                        case 'сорта винограда':
                            $normalized['grapes'] = $val;
                            break;
                        case 'подходит к':
                            $normalized['pairing'] = $val;
                            break;
                        default:
                            $normalized['attributes'][$key] = $val;
                    }
                }
            }
        }

        // 🔍 Обработка JSON-описания (вкус, аромат, цвет, сочетания)
        if (!empty($normalized['ws_about_product'])) {
            $about = json_decode($normalized['ws_about_product'], true);
            if (is_array($about)) {
                foreach ($about as $section) {
                    $title = mb_strtolower(trim($section['title'] ?? ''));
                    $text = trim($section['text'] ?? '');
                    if (!$title || !$text) continue;

                    if (in_array($title, ['вкус', 'аромат'])) {
                        $normalized['wine_tastes'][] = $text;
                    } elseif ($title === 'сочетания') {
                        $normalized['pairing'] = ($normalized['pairing'] ?? '') . ' ' . $text;
                    } elseif ($title === 'цвет') {
                        $normalized['attributes']['Цвет'] = $text;
                    }
                }
            }
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
        if (empty($name)) return null;
        $item = $model::firstOrNew(['name->ru' => $name]);
        $item->fill(['name' => ['ru' => $name, 'en' => $name]]);
        if (empty($item->region_id)) {
            $item->region_id = $regionId;
        }
        $item->save();
        return $item->id;
    }

    public function chunkSize(): int
    {
        return 200;
    }
    protected function detectCategoryFromName(string $name): ?Category
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
            'водка плодовая' => 'ВОДКА',
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
            'вода минеральная' => 'ВОДА И ЛИМОНАДЫ',
            'тоник' => 'ВОДА И ЛИМОНАДЫ',
            'сироп' => 'ВОДА И ЛИМОНАДЫ',
            'сок' => 'ВОДА И ЛИМОНАДЫ',
            'нектар' => 'ВОДА И ЛИМОНАДЫ',
            'морс' => 'ВОДА И ЛИМОНАДЫ',

            // Продукты
            'масло' => 'ПРОДУКТЫ',
            'оливковое масло' => 'ПРОДУКТЫ',
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

        $normalized = mb_strtolower($name);

        foreach ($categoryMap as $keyword => $categoryName) {
            if (str_contains($normalized, $keyword)) {

                // 🔍 ищем категорию без учёта регистра
                $existing = Category::whereRaw(
                    'LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru"))) = ?',
                    [mb_strtolower($categoryName)]
                )->first();

                if ($existing) {
                    return $existing;
                }

                // если нет — просто возвращаем null (не создаём новую!)
                return null;
            }
        }

        // если ничего не нашли, возвращаем категорию "ПРОЧЕЕ", если она уже есть
        return Category::whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru"))) = ?', ['прочее'])->first();
    }



    protected function detectOrCreateRegion(?string $country, ?string $region): ?int
    {
        // 🧠 Если регион содержит запятую — разделяем на страну и регион
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

        // 🏳️ Создаём или ищем страну
        $countryRegion = null;
        if ($country) {
            $countryRegion = \App\Models\Region::firstOrCreate(
                ['name->ru' => $country],
                ['name' => ['ru' => $country, 'en' => $country], 'parent_id' => null]
            );
        }

        // 🏞️ Создаём или ищем дочерний регион
        if ($region) {
            $childRegion = \App\Models\Region::firstOrCreate(
                ['name->ru' => $region, 'parent_id' => $countryRegion?->id],
                [
                    'name' => ['ru' => $region, 'en' => $region],
                    'parent_id' => $countryRegion?->id,
                ]
            );

            return $childRegion->id;
        }

        // 🇷🇺 Если только страна
        return $countryRegion?->id;
    }


}
