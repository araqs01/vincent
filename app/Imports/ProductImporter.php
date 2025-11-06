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

class ProductImporter implements ToCollection, WithChunkReading, WithBatchInserts, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function collection(Collection $rows)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0); // бесконечное время, так как работает в очереди

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
            try {
                foreach ($dataRows as $index => $row) {
                    $assoc = $this->combineRowWithHeaders($headers, $row->toArray());
                    $normalized = $this->normalizeRow($assoc);
                    if (empty($normalized)) continue;

                    if (!empty($normalized['ws_characteristics'])) {
                        try {
                            $chars = $normalized['ws_characteristics'];
                            if (is_string($chars)) {
                                $fixed = trim($chars);
                                if ($fixed === '' || $fixed === '[]' || $fixed === '[ ]') {
                                    $chars = [];
                                } else {
                                    if (str_starts_with($fixed, '[') && str_contains($fixed, "'")) {
                                        $fixed = str_replace("'", '"', $fixed);
                                    }
                                    $fixed = preg_replace('/,\s*([\]}])/m', '$1', $fixed);

                                    $chars = json_decode($fixed, true);

                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                        $chars = [];
                                    }
                                }
                            } elseif (!is_array($chars)) {
                                $chars = [];
                            }

                            if (!empty($chars) && is_array($chars)) {
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
                                        case 'cорта винограда': // латинская c
                                        case 'виноград':
                                        case 'grape':
                                        case 'grapes':
                                            $normalized['grapes'] = trim(($normalized['grapes'] ?? '') . ', ' . $val, ', ');
                                            break;
                                            case 'подходит к':
                                        case 'гастрономические сочетания':
                                            $normalized['pairing'] = trim(($normalized['pairing'] ?? '') . ', ' . $val, ', ');
                                            break;

                                        case 'аромат':
                                        case 'характер':
                                        case 'вкус':
                                        case 'тело':
                                        case 'кислотность':
                                            $normalized['wine_tastes'] = trim(($normalized['wine_tastes'] ?? '') . ', ' . $val, ', ');
                                            break;
                                            case 'крепость':
                                        case 'насыщенность':
                                        case 'глубина цвета':
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
                            \Log::warning('Ошибка обработки ws_characteristics: ' . $e->getMessage());
                        }
                    }

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

                            if (is_array($aboutSections)) {
                                foreach ($aboutSections as $section) {
                                    if (!is_array($section)) continue;

                                    $title = trim($section['title'] ?? '');
                                    $text = trim($section['text'] ?? '');

                                    if ($title && $text) {
                                        $metaSections[] = [
                                            'title' => $title,
                                            'text' => $text,
                                        ];
                                    }
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Ошибка обработки ws_about_product: ' . $e->getMessage());
                        }
                    }

                    foreach (['wine_tastes', 'pairing'] as $field) {
                        if (!empty($normalized[$field])) {
                            $normalized[$field] = trim($normalized[$field], ", \t\n\r\0\x0B");
                        }
                    }

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

                    $nameRu = $normalized['name_price'] ?? $normalized['name_ru'] ?? null;
                    $nameEn = $normalized['name_price_en'] ?? null;
                    $nameWithYear = $normalized['name_price_year'] ?? $nameRu;

                    [$baseName, $volume, $vintage] = $this->parseNameVolumeAndVintage($nameWithYear);
                    $baseName = trim(preg_replace('/\s{2,}/', ' ', preg_replace('/[\/\\\()\[\]\d.,]+$/u', '', $baseName)));
                    $slug = Str::slug(Str::limit($baseName, 80, ''));

                    $descriptionRu = trim(($normalized['about'] ?? '') . "\n\n" . ($normalized['description'] ?? ''));
                    $descriptionEn = $normalized['description_en'] ?? null;
                    $price = $this->sanitizePrice($normalized['price'] ?? null);

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

                    $imageUrl = $normalized['image_link'] ?? $normalized['foto'] ?? null;
                    if (empty($imageUrl)) {
                        $imageUrl = 'https://s2.wine.style/images_gen/116/11675/0_0_695x600.webp';
                    }
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
            } catch (\Throwable $e) {
                Log::error("💥 Ошибка при обработке строки {$index}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });


    }

    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Количество строк, вставляемых за один запрос
     * 200-500 — оптимально
     */
    public function batchSize(): int
    {
        return 300;
    }

    /**
     * Опционально: количество попыток выполнения задачи в очереди
     */
    public $tries = 3;

    /**
     * Увеличиваем таймаут очереди (в секундах)
     */
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


}
