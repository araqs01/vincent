<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductTaste extends Pivot
{
    protected $table = 'product_taste';
    protected $fillable = ['product_id', 'taste_id', 'intensity_percent'];

    protected $casts = [
        'intensity_percent' => 'decimal:2',
    ];
}
