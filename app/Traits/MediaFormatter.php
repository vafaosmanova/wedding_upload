<?php

namespace App\Traits;

use Illuminate\Support\Facades\Route;

trait MediaFormatter
{
    public function formatMediaCollectionGuest($media, string $type)
    {
        $routeName = 'guest.media.stream';
        return $media->map(fn($m) => [
            'id' => $m->id,
            'filename' => $m->filename,
            'url' => route($routeName, [
                'album_id' => $m->album_id,
                'type' => $type,
                'media_id' => $m->id
            ]),
            'approved' => $m->approved,
            'type'     => $type,
        ]);
    }
    public function formatMediaCollectionOwner($media, string $type, string $routeName = null)
    {
        $routeName = $routeName ?? 'owner.media.stream';

        return $media->map(fn($m) => [
            'id' => $m->id,
            'filename' => $m->filename,
            'url' => route($routeName, [
                'album_id' => $m->album_id,
                'type' => $type,
                'media_id' => $m->id
            ]),
            'approved' => $m->approved,
            'type'     => $type,
        ]);
    }
}

