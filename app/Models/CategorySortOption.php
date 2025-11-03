<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CategorySortOption extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'group_id',
        'key',
        'title',
        'field',
        'type',
        'direction',
        'ui_type',
        'meta',
        'is_active',
        'order_index',
    ];

    public $translatable = ['title'];

    protected $casts = [
        'title' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(CategorySortGroup::class, 'group_id');
    }
}
