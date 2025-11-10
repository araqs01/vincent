<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WhiskyBase extends Model
{
    use HasTranslations;

    protected $table = 'whiskies_base';

    protected $fillable = [
        'name',
        'manufacturer',
        'is_blended',
        'sweetness',
        'smoky',
        'fruity',
        'spicy',
        'floral',
        'woody',
        'grainy',
        'creamy',
        'sulphury',
        'smooth',
        'finish_length',
        'bitterness',
        'dryness',
        'body',
        'country',
        'for_cigar',
        'blend_included',
        'blend_with',
        'awards',
        'aroma',
        'taste',
        'aftertaste',
        'meta',
    ];

    public $translatable = [
        'name',
        'manufacturer',
        'country',
    ];

    protected $casts = [
        'is_blended' => 'boolean',
        'for_cigar' => 'boolean',
        'blend_included' => 'array',
        'blend_with' => 'array',
        'awards' => 'array',
        'meta' => 'array',
    ];
}
