<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WhiskyTasteGroup extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'type'];

    public $translatable = ['name', 'type'];

    protected $casts = [
        'name' => 'array',
        'type' => 'array',
    ];

    public function tastes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WhiskyTaste::class, 'group_id');
    }
}
