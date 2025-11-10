<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class StrongDrinkDish extends Model
{
    use HasTranslations;

    protected $fillable = [
        'type',
        'age',
        'class',
        'taste_tags',
        'strength',
        'drink_type',
        'dishes',
    ];

    public $translatable = ['type'];

    protected $casts = [
        'taste_tags' => 'array',
        'dishes' => 'array',
        'type' => 'array',
    ];
}
