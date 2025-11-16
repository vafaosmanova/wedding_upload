<?php

namespace App\Traits;
trait MediaFormatter
{
    public function formatMediaCollection($media, string $type)
    {
        return $media->map(fn($m) => [
            'id'       => $m->id,
            'name'     => $m->filename,
            'url'      => route('api.media.show', [
                'album_id' => $m->album_id,
                'filename' => $m->filename,
            ]),
            'approved' => $m->approved,
            'type'     => $type,
        ]);
    }



}
