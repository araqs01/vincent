<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CategoryFilter extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'category_id',
        'key',
        'title',
        'mode',
        'source_model',
        'config',
        'is_active',
        'order_index',
        'ui_type',
    ];

    public $translatable = ['title'];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];


    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CategoryFilterOption::class, 'filter_id');
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTranslatedTitle(string $locale = null): string
    {
        return $this->getTranslation('title', $locale ?? app()->getLocale());
    }
}
