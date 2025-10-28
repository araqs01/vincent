<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Region extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;


    protected $fillable = [
        'parent_id',
        'name',
        'description',
    ];

    public $translatable = ['name', 'description'];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero_image')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('icon_terroir')->singleFile();
        $this->addMediaCollection('icon_production')->singleFile();
    }

}
