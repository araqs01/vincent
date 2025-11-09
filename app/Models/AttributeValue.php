<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AttributeValue extends Model
{
    use HasTranslations;

    protected $fillable = [
        'attribute_id',
        'value',
    ];

    protected $casts = [
        'value' => 'json', // потому что значения могут быть разными типами
    ];

    public $translatable = ['value'];


    public function attribute(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_value')
            ->withTimestamps();
    }
}
