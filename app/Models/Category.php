<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;
    use HasSlug;
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'type',
        'description',
    ];

    public  $translatable = ['name', 'description'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->getTranslation('name', app()->getLocale()))
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(60);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(\App\Models\Attribute::class, 'category_attribute')
            ->withPivot('is_required', 'order_index')
            ->withTimestamps();
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function menuBanners(): HasMany
    {
        return $this->hasMany(MenuBanner::class);
    }


    public function menuBlocks(): HasMany
    {
        return $this->hasMany(MenuBlock::class);
    }
}
