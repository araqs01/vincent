<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SommelierGroup extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'slug', 'order_index'];
    public $translatable = ['name'];

    public function tags()
    {
        return $this->hasMany(SommelierTag::class, 'group_id');
    }
}
