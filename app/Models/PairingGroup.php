<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PairingGroup extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'description', 'meta'];
    public $translatable = ['name', 'description'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pairings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pairing::class);
    }
}

