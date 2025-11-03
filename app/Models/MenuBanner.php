<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MenuBanner extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'title',
        'filter_key',
        'order_index',
        'is_active',
    ];

    public $translatable = ['title'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
