<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand_id',
        'brand_line_id',
        'region_id',
        'supplier_id',
        'price',
        'final_price',
        'rating',
        'status',
        'description',
        'meta',
        'manufacturer_id',
        'alcohol_strength'
    ];

    public $translatable = ['name', 'description'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function brandLine(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BrandLine::class);
    }

    public function region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function manufacturer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }


// ✅ Прямая связь с attribute_values
    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_attribute_value',
            'product_id',
            'attribute_value_id'
        )
            ->withTimestamps()
            ->with(['attribute']); // сразу подгружаем attribute
    }

// ✅ Удобная "через" связь для получения самих атрибутов
    public function attributes()
    {
        return $this->hasManyThrough(
            Attribute::class,
            AttributeValue::class,
            'id',             // local key в attribute_values
            'id',             // local key в attributes
            null,
            'attribute_id'    // foreign key attribute_values.attribute_id → attributes.id
        );
    }


    public function filterOptions()
    {
        return $this->belongsToMany(CategoryFilterOption::class, 'product_filter_option', 'product_id', 'category_filter_option_id')
            ->withTimestamps();
    }

    public function tastes()
    {
        return $this->belongsToMany(Taste::class, 'product_taste')
            ->withPivot('intensity_percent')
            ->select('tastes.*') // выбираем только уникальные taste
            ->distinct('tastes.id'); // 👈 DISTINCT по ID taste
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'product_dish')
            ->withPivot('match_percent');
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product');
    }

    public function grapes()
    {
        return $this->belongsToMany(Grape::class, 'product_grape')
            ->withPivot('percent', 'main')
            ->withTimestamps();
    }


    public function grapeVariants()
    {
        return $this->belongsToMany(GrapeVariant::class, 'product_grape_variant')
            ->withPivot('percent', 'main')
            ->withTimestamps();
    }

    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function pairings()
    {
        return $this->belongsToMany(Pairing::class, 'product_pairing')
            ->withTimestamps();
    }


    public function getLocalizedName(): string
    {
        return $this->getTranslation('name', app()->getLocale());
    }

    public function getLocalizedDescription(): ?string
    {
        return $this->getTranslation('description', app()->getLocale());
    }

    public function getFullBrandName(): string
    {
        if ($this->brand && $this->brandLine) {
            return "{$this->brand->getTranslation('name', app()->getLocale())} {$this->brandLine->getTranslation('name', app()->getLocale())}";
        }

        return $this->brand?->getTranslation('name', app()->getLocale()) ?? '';
    }

    public function hasDiscount(): bool
    {
        return !is_null($this->final_price) && $this->final_price < $this->price;
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->useDisk('public') // или 'media' если у тебя отдельный диск
            ->singleFile(); // если нужно хранить только одно фото
    }

    public function getShortSpecsAttribute(): array
    {
        $parts = [];

        // 🏳️ Страна = родитель региона
        if ($this->region) {
            $region = $this->region->parent
                ? $this->region->getTranslation('name', app()->getLocale())
                : null;

            $parts[] = $region ?? null ;
        }

        // 🎨 Цвет (атрибут)
        $color = $this->attributeValues
            ->firstWhere('attribute.slug', 'cvet-vina')
            ?->getTranslation('value', app()->getLocale());

        if ($color) {
            $parts[] = ucfirst($color);
        }

        // 🍯 Сахар (атрибут)
        $sugar = $this->attributeValues
            ->firstWhere('attribute.slug', 'tip-saxar')
            ?->getTranslation('value', app()->getLocale());

        if ($sugar) {
            $parts[] = ucfirst($sugar);
        }

        // 💪 Крепость
        if ($this->alcohol_strength) {
            $parts[] = rtrim(rtrim(number_format($this->alcohol_strength, 1, '.', ''), '0'), '.') . '%';
        }

        if ($this->grapes->isNotEmpty()) {
            $firstGrape = ucfirst($this->grapes->first()->getTranslation('name', app()->getLocale()));
            $parts[] = $firstGrape;
        }


        return $parts;
    }

    public function getFullSpecsAttribute(): array
    {
        $parts = [];

        // 🌍 Страна
        if ($this->region?->parent?->getTranslation('name', app()->getLocale())) {
            $parts[] = $this->region->parent->getTranslation('name', app()->getLocale());
        }

        // 🏞️ Подрегион (если есть)
        if ($this->region && $this->region?->parent) {
            $parts[] = $this->region->getTranslation('name', app()->getLocale());
        }

        // 🎨 Цвет
        $color = $this->attributeValues
            ->firstWhere('attribute.slug', 'cvet-vina')
            ?->getTranslation('value', app()->getLocale());
        if ($color) {
            $parts[] = ucfirst($color);
        }

        // 🍯 Сахар
        $sugar = $this->attributeValues
            ->firstWhere('attribute.slug', 'tip-saxar')
            ?->getTranslation('value', app()->getLocale());
        if ($sugar) {
            $parts[] = ucfirst($sugar);
        }

        // 💪 Крепость (%)
        if (!empty($this->alcohol_strength)) {
            $parts[] = rtrim($this->alcohol_strength, '%') . '%';
        }

        // 🍇 Сорта винограда (все, через точку)
        if ($this->grapes->isNotEmpty()) {
            $parts[] = $this->grapes->take(2)->pluck('name')->map(fn($n) => ucfirst($n))->join(' • ');
        }

        return $parts;
    }


}
