<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    // Upload-Funktion für Fotos und Videos
    public function upload(Request $request, $album_id)
    {
        $request->validate([
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',  // max 5 MB pro Foto
            'videos.*' => 'mimes:mp4,mov|max:51200',            // max 50 MB pro Video
        ]);

        $album = Album::findOrFail($album_id);
        $disk = 'hetzner';
        $uploaded = collect();

        // Fotos speichern
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store("albums/{$album->id}/photos", $disk);
                $media = $album->photos()->create([
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'approved' => true,
                ]);

                $uploaded->push([
                    'id' => $media->id,
                    'filename' => $media->filename,
                    'url' => Storage::disk($disk)->url($path),
                    'type' => 'photo',
                ]);
            }
        }

        // Videos speichern
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $file) {
                $path = $file->store("albums/{$album->id}/videos", $disk);
                $media = $album->videos()->create([
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'approved' => true,
                ]);

                $uploaded->push([
                    'id' => $media->id,
                    'filename' => $media->filename,
                    'url' => Storage::disk($disk)->url($path),
                    'type' => 'video',
                ]);
            }
        }

        return response()->json([
            'message' => 'Upload erfolgreich',
            'media' => $uploaded->values(),
        ]);
    }
}
