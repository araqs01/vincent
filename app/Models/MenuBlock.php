<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class MenuBlock extends Model
{
    use HasTranslations;

    protected $fillable = [
        'category_id',
        'title',
        'type',
        'order_index',
        'is_active',
    ];

    public $translatable = ['title'];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function values(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MenuBlockValue::class, 'menu_block_id');
    }
}
