<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CategorySortGroup extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'category_id',
        'key',
        'title',
        'ui_type',
        'is_active',
        'order_index',
    ];

    public $translatable = ['title'];

    protected $casts = [
        'title' => 'array',
        'is_active' => 'boolean',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CategorySortOption::class, 'group_id');
    }
}
