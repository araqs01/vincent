<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Supplier extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'contact_info',
        'min_order',
        'delivery_time',
        'rating',
    ];

    public $translatable = ['name'];

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }
}
