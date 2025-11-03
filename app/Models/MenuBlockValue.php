<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class MenuBlockValue extends Model
{
    use HasTranslations;

    protected $fillable = [
        'menu_block_id',
        'value',
        'order_index',
        'is_active',
    ];

    public $translatable = ['value'];

    public function block(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MenuBlock::class, 'menu_block_id');
    }
}
