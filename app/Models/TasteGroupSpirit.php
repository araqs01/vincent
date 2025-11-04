<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TasteGroupSpirit extends Model
{
    use HasTranslations;

    protected $table = 'taste_group_spirits';

    protected $fillable = [
        'name',
        'description',
        'image',
        'meta',
    ];

    public $translatable = ['name', 'description'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tastes()
    {
        return $this->hasMany(Taste::class, 'taste_group_spirit_id');
    }
}
