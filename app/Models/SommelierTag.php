<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SommelierTag extends Model
{
    use HasTranslations;

    protected $fillable = ['group_id', 'name', 'slug', 'order_index'];
    public $translatable = ['name'];

    public function group()
    {
        return $this->belongsTo(SommelierGroup::class, 'group_id');
    }
}

