<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class DishGroup extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'description',
    ];

    public $translatable = ['name', 'description'];

    public function dishes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dish::class);
    }
}
