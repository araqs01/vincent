<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WineTasteGroup extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'type',
        'final_group',
        'meta',
    ];

    public $translatable = [
        'name',
        'type',
        'final_group',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tastes()
    {
        return $this->hasMany(WineTaste::class, 'group_id');
    }
}
