<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrapeVariant extends Model
{
    protected $fillable = ['grape_id', 'region_id', 'category_id', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function grape()
    {
        return $this->belongsTo(Grape::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tastes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Taste::class)
            ->withPivot('order_index')
            ->orderByPivot('order_index'); // чтобы Laravel возвращал в правильном порядке
    }

    public function pairings()
    {
        return $this->belongsToMany(Pairing::class, 'grape_variant_pairing');
    }
}
