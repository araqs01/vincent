<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class WhiskyTaste extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'group',
        'type',
        'weight',
        'group_id',
    ];

    public $translatable = ['name', 'group', 'type'];

    protected $casts = [
        'name' => 'array',
        'group' => 'array',
        'type' => 'array',
    ];

    public function groupRelation()
    {
        return $this->belongsTo(WhiskyTasteGroup::class, 'group_id');
    }
}
