<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TasteGroup extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'name_en',
        'description',
    ];

    public $translatable = ['name', 'description'];

    public function tastes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Taste::class);
    }
}
