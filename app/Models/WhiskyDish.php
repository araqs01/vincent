<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WhiskyDish extends Model
{
    use HasTranslations;

    protected $fillable = [
        'type',
        'region',
        'sweetness',
        'smokiness',
        'fruitiness',
        'strength',
        'spiciness',
        'astringency',
        'body',
        'age',
        'tags',
        'snacks',
    ];

    public $translatable = ['type', 'region'];

    protected $casts = [
        'sweetness' => 'array',
        'smokiness' => 'array',
        'fruitiness' => 'array',
        'strength' => 'array',
        'spiciness' => 'array',
        'astringency' => 'array',
        'body' => 'array',
        'tags' => 'array',
        'snacks' => 'array',
    ];
}
