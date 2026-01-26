<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\MediaFormatter;

class UploadController extends Controller
{
    use MediaFormatter;

    public function upload(Request $request, int $album_id, bool $isGast = false)
    {
        $request->validate([
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',  // 5 MB
            'videos.*' => 'mimes:mp4,mov,avi,webm|max:51200',  // 50 MB
        ]);

        $album = Album::findOrFail($album_id);
        $disk = 'hetzner';
        $uploaded = collect();

        $routeName = $isGast ? 'gast.media.stream' : 'user.media.stream';

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store("albums/{$album->id}/photos", $disk);

                $media = $album->media()->create([
                    'path'      => $path,
                    'filename'  => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'approved'  => false,
                ]);

                $uploaded->push(
                    $this->formatMediaCollectionUser(collect([$media]), 'image', $routeName)->first()
                );
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $file) {
                $path = $file->store("albums/{$album->id}/videos", $disk);

                $media = $album->media()->create([
                    'path'      => $path,
                    'filename'  => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'approved'  => false,
                ]);

                $uploaded->push(
                    $this->formatMediaCollectionUser(collect([$media]), 'video', $routeName)->first()
                );
            }
        }

        return response()->json([
            'message' => 'Upload erfolgreich',
            'media'   => $uploaded,
        ]);
    }
}
