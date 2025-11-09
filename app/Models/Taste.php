<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Taste extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;

    protected $fillable = ['name', 'taste_group_id','taste_group_spirit_id'];
    public $translatable = ['name'];

    public function group()
    {
        return $this->belongsTo(TasteGroup::class, 'taste_group_id','id');
    }

    public function groupSpirit()
    {
        return $this->belongsTo(TasteGroupSpirit::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_taste')
            ->withPivot('intensity_percent')
            ->withTimestamps();
    }

    public function grapeVariants()
    {
        return $this->belongsToMany(GrapeVariant::class, 'grape_variant_taste');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('hero_image')
            ->singleFile();
    }
}

