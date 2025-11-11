<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WineDish extends Model
{
    use HasTranslations;

    protected $fillable = [
        'category_id',
        'type',
        'color',
        'name',
        'grape_mix',
        'region_id',
        'pairings',
        'aromaticity',
        'sweetness',
        'body',
        'tannin',
        'acidity',
        'effervescence',
        'strength_min',
        'strength_max',
        'age_min',
        'age_max',
        'sugar',
        'price_min',
        'price_max',
        'extra_marker',
        'meta',
        'grouping'
    ];

    public $translatable = ['name', 'grape_mix', 'pairings'];

    protected $casts = [
        'pairings' => 'array',
        'meta'=>'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function grapes()
    {
        return $this->belongsToMany(Grape::class, 'grape_wine_dish');
    }
}
