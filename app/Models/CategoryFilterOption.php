<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CategoryFilterOption extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'filter_id',
        'value',
        'slug',
        'meta',
        'is_active',
        'order_index',
        'show_in_header'
    ];

    public $translatable = ['value'];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];


    public function filter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CategoryFilter::class, 'filter_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_filter_option', 'category_filter_option_id', 'product_id');
    }


    public function getTranslatedValue(string $locale = null): string
    {
        return $this->getTranslation('value', $locale ?? app()->getLocale());
    }
}
