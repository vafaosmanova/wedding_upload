<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    // Alle genehmigten Medien eines Albums anzeigen
    public function index($album_id)
    {
        $photos = Photo::where('album_id', $album_id)->where('approved', true)->get();
        $videos = Video::where('album_id', $album_id)->where('approved', true)->get();

        $media = $photos->map(fn($p) => [
            'id' => $p->id,
            'filename' => $p->filename,
            'type' => 'photo',
            'url' => Storage::disk('hetzner')->url($p->path)
        ])->concat(
            $videos->map(fn($v) => [
                'id' => $v->id,
                'filename' => $v->filename,
                'type' => 'video',
                'url' => Storage::disk('hetzner')->url($v->path)
            ])
        );

        return response()->json(['media' => $media->values()]);
    }

    // Datei herunterladen / anzeigen
    public function show($album_id, $filename)
    {
        $disk = 'hetzner';
        $path = "albums/{$album_id}/{$filename}";

        if (!Storage::disk($disk)->exists($path)) {
            abort(404, "Datei {$filename} nicht gefunden");
        }

        return response()->file(Storage::disk($disk)->path($path));
    }

    // Upload von Fotos/Videos
    public function upload(Request $request, $album_id)
    {
        $request->validate([
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'videos.*' => 'mimes:mp4,mov|max:51200',
        ]);

        $album = Album::findOrFail($album_id);

        $uploaded = collect();

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store("albums/{$album->id}/photos", 'hetzner');
                $media = $album->photos()->create([
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'approved' => true
                ]);
                $uploaded->push([
                    'id' => $media->id,
                    'filename' => $media->filename,
                    'url' => Storage::disk('hetzner')->url($path),
                    'type' => 'photo'
                ]);
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $file) {
                $path = $file->store("albums/{$album->id}/videos", 'hetzner');
                $media = $album->videos()->create([
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'approved' => true
                ]);
                $uploaded->push([
                    'id' => $media->id,
                    'filename' => $media->filename,
                    'url' => Storage::disk('hetzner')->url($path),
                    'type' => 'video'
                ]);
            }
        }

        return response()->json([
            'message' => 'Upload erfolgreich',
            'media' => $uploaded->values()
        ]);
    }
}
