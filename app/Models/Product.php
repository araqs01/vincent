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


    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_value')
            ->withPivot('value')
            ->withTimestamps();
    }


    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value')
            ->withTimestamps();
    }


    public function filterOptions()
    {
        return $this->belongsToMany(CategoryFilterOption::class, 'product_filter_option', 'product_id', 'category_filter_option_id')
            ->withTimestamps();
    }

    public function tastes()
    {
        return $this->belongsToMany(Taste::class, 'product_taste')
            ->withPivot('intensity_percent');
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

    public function variants()
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
}
