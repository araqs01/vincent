<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class PairingGroup extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;

    protected $fillable = ['name', 'description', 'meta'];
    public $translatable = ['name', 'description'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pairings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pairing::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->useDisk('public') // или 'media' если у тебя отдельный диск
            ->singleFile(); // если нужно хранить только одно фото
    }
}

