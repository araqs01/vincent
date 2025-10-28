<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Taste extends Model
{
    use HasTranslations;

    protected $fillable = [
        'taste_group_id',
        'name',
        'name_en',
        'weight',
    ];

    public $translatable = ['name'];

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TasteGroup::class, 'taste_group_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_taste')
            ->withPivot('intensity_percent');
    }
}
