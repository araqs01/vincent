<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductGrape extends Pivot
{
    protected $table = 'product_grape';

    protected $fillable = [
        'product_id',
        'grape_id',
        'percent',
        'main',
    ];

    protected $casts = [
        'percent' => 'decimal:2',
        'main' => 'boolean',
    ];
}
