<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use App\Models\Album;

class UploadController extends Controller
{
    /** Uploads sowohl für Besitzer (Sanctum Session) als auch für Gäste (X-Gast-Token) */
    public function upload(Request $request, $albumId)
    {
        $album = Album::findOrFail($albumId);
        $isGuest = false;
        $token = $request->header('X-Gast-Token');

        if ($token) {
            $redisAlbumId = Redis::get("guest_token:{$token}");
            if (!$redisAlbumId || $redisAlbumId != $albumId) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            $isGuest = true;
        } else {
            if (!auth()->check() || $album->user_id !== auth()->id()) {
                return response()->json(['message' => 'Nicht autorisiert'], 403);
            }
        }
        $rules = [
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120', // 5MB
            'videos.*' => 'mimetypes:video/mp4,video/quicktime|max:51200', // 50MB
        ];
        $request->validate($rules);

        $uploadedFiles = ['photos' => [], 'videos' => []];

        $disk = 'hetzner';
        $approved = !$isGuest;

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('albums/photos', $disk);
                $photo = $album->photos()->create([
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'approved' => $approved,
                ]);
                $uploadedFiles['photos'][] = [
                    'id' => $photo->id,
                    'filename' => $photo->filename,
                    'url' => Storage::disk($disk)->url($photo->path),
                    'approved' => $photo->approved,
                ];
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $file) {
                $path = $file->store('albums/videos', $disk);
                $video = $album->videos()->create([
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'approved' => $approved,
                ]);
                $uploadedFiles['videos'][] = [
                    'id' => $video->id,
                    'filename' => $video->filename,
                    'url' => Storage::disk($disk)->url($video->path),
                    'approved' => $video->approved,
                ];
            }
        }

        return response()->json([
            'message' => 'Upload erfolgreich',
            'uploaded' => $uploadedFiles,
        ]);
    }
}
