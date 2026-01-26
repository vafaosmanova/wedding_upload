<?php

namespace App\Traits;

use Illuminate\Support\Facades\Route;

trait MediaFormatter
{
    public function formatMediaCollectionGast($media, string $type)
    {
        $routeName = 'gast.media.stream';
        return $media->map(fn($m) => [
            'id' => $m->id,
            'filename' => $m->filename,
            'url' => route($routeName, [
                'album_id' => $m->album_id,
                'type' => $type,
                'media_id' => $m->id
            ]),
            'approved' => false,
            'type'     => $type,
        ]);
    }
    public function formatMediaCollectionUser($media, string $type, string $routeName = null)
    {
        $routeName = $routeName ?? 'user.media.stream';

        return $media->map(fn($m) => [
            'id' => $m->id,
            'filename' => $m->filename,
            'url' => route($routeName, [
                'album_id' => $m->album_id,
                'type' => $type,
                'media_id' => $m->id
            ]),
            'approved' => false,
            'type'     => $type,
        ]);
    }
}

