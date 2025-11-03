<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Grape extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'description'];
    public $translatable = ['name', 'description'];

    public function variants()
    {
        return $this->hasMany(GrapeVariant::class);
    }
}


