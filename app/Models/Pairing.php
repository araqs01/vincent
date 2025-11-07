<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Pairing extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'body',
        'meta',
        'pairing_group_id',
    ];

    public $translatable = ['name', 'description', 'body'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(PairingGroup::class, 'pairing_group_id');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->singleFile(); // если нужно хранить только одно фото
    }
}
