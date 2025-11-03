<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Taste extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'taste_group_id'];
    public $translatable = ['name'];

    public function group()
    {
        return $this->belongsTo(TasteGroup::class, 'taste_group_id');
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

}

