<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Brand extends Model
{
    use HasTranslations;
    use HasSlug;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'country',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->getTranslation('name', app()->getLocale()))
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(60);
    }

    public $translatable = ['name', 'description'];

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }
}
