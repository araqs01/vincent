<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class TasteGroup extends Model implements HasMedia
{
    use HasTranslations;
    use HasSlug;
    use InteractsWithMedia;

    protected $fillable = [
        'slug',
        'name',
        'name_en',
        'description',
    ];

    public $translatable = ['name', 'description'];

    public function tastes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Taste::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn() => $this->getTranslation('name', 'en') ?? $this->getTranslation('name', 'ru'))
            ->saveSlugsTo('slug');
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, Taste::class, 'taste_group_id', 'id', 'id', 'id');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->singleFile();
    }
}
