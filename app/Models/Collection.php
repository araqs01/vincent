<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Collection extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'filter_formula',
        'is_auto',
        'description',
    ];

    public $translatable = ['name', 'description'];

    protected $casts = [
        'filter_formula' => 'array',
        'is_auto' => 'boolean',
    ];

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product');
    }
}
