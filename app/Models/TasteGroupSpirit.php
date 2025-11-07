<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class TasteGroupSpirit extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;

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

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->singleFile();
    }
}
