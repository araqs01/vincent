<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WineDish extends Model
{
    use HasTranslations;

    protected $fillable = [
        'category_id',
        'type',        // например: 'игристое', 'брют', 'шампанское'
        'color',
        'name',
        'grape_mix',
        'region_id',
        'pairings',
    ];

    public $translatable = ['name', 'grape_mix', 'pairings'];

    protected $casts = [
        'pairings' => 'array',
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
