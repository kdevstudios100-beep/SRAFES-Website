<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use SplFileInfo;

class MediaAsset extends Model
{
    use HasFactory;
    use \Sushi\Sushi;

    protected $fillable = [
        'extension',
        'url',
        'filename',
        'path',
        'size',
        'dimentions',
    ];

    public function getRows()
    {
        return collect(File::allFiles(public_path('images')))
            ->filter(fn (SplFileInfo $splFileInfo) => $splFileInfo->getExtension() != 'svg')
            ->map(function (SplFileInfo $splFileInfo) {
                [$width, $height] = getimagesize($splFileInfo->getRealPath());

                return [
                    'extension' => $splFileInfo->getExtension() === 'jpg' ? 'jpeg' : $splFileInfo->getExtension(),
                    'url' => asset('images/'.$splFileInfo->getFilename()).'?v='.time(),
                    'filename' => $splFileInfo->getFilename(),
                    'path' => $splFileInfo->getRealPath(),
                    'size' => round($splFileInfo->getSize() / 1000),
                    'dimentions' => "{$width}x{$height}",
                ];
            })
            ->toArray();
    }
}
