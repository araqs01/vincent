<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Dish extends Model
{
    use HasTranslations;

    protected $fillable = [
        'dish_group_id',
        'name',
    ];

    public $translatable = ['name'];

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DishGroup::class, 'dish_group_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_dish')
            ->withPivot('match_percent');
    }
}
