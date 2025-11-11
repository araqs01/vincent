<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WineTaste extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'group_id',
        'meta',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(WineTasteGroup::class, 'group_id');
    }
}
