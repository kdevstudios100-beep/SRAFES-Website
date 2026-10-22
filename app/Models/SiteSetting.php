<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteSetting extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'settings',
        'gallery_collections',
    ];

    public function getCasts()
    {
        return [
            'settings' => AsCollection::class,
            'gallery_collections' => AsCollection::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        ($this->gallery_collections ?? collect([]))
            ->each(function ($collection) {
                $this->addMediaCollection($collection['name']);
            });
    }
}
