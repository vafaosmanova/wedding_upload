<?php

namespace App\Http\Controllers;


use App\Jobs\ExportAlbumJob;
use App\Models\Album;
use App\Models\Pin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::where('user_id', auth()->id())->get();
        return response()->json($albums);
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'pin' => 'required|string|min:4|max:10',
        ]);
        $titleExists = Album::where('title', $request->title)
            ->where('user_id', auth()->id())
            ->exists();

        if ($titleExists) {
            return response()->json(['message' => 'Ein Album mit diesem Namen existiert bereits.'],
                422);
        }

        $pinExists = Pin::where('pin', $request->pin)
            ->whereHas('album', fn($q) => $q->where('user_id', auth()->id()))->exists();

        if ($pinExists) {
            return response()->json(['message' => 'Ein Album mit dieser PIN existiert bereits.'], 422);
        }
        try {
            $album = Album::createWithQr(['title' => $request->title, 'user_id' => auth()->id(),]);
            Pin::create(['pin' => $request->pin, 'album_id' => $album->id,]);
            return response()->json(['album' => $album, 'qr_code' => $album->qr_code, 'pin' => $request->pin,], 201);
        } catch (Throwable $e) {
            Log::error("Fehler beim Erstellen des Albums: " . $e->getMessage());
            return response()->json(['message' => 'Fehler beim Erstellen des Albums'], 500);
        }
    }

    public function update(Request $request, $album_id)
    {
        $album = Album::findOrFail($album_id);
        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Zugriff verweigert'], 403);
        }
        $request->validate(['title' => 'nullable|string|max:255', 'pin' => 'nullable|string|min:4|max:10']);
        if($request->has('title')) {
            $album->update(['title'=>$request->title]);
        }
        if ($request->has('pin')) {
            $pinExists = Pin::where('pin', $request->pin)
                ->whereHas('album', fn($q) =>
                $q->where('user_id', auth()->id())
                    ->where('id', '!=', $album->id)
                )->exists();

            if ($pinExists) {
                return response()->json([
                    'message' => 'Diese PIN wird bereits verwendet.'
                ], 422);
            }
        }

    }
    public function destroy($album_id)
    {
        $album = Album::findOrFail($album_id);
        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Zugriff verweigert'], 403);
        }
        foreach ($album->media as $mediaItem) {
            Storage::disk('hetzner')->delete($mediaItem->path);
        }
        $album->delete();
        return response()->json(['message' => 'Album gelöscht']);
    }

    public function exportAlbum(int $album_id)
    {
        $album = Album::findOrFail($album_id);

        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Zugriff verweigert'], 403);
        }
        try {
            ExportAlbumJob::dispatch($album_id)->onQueue('exports');

            return response()->json([
                'message' => 'Export gestartet',
                'album_id' => $album_id,
            ]);

        } catch (Throwable $e) {
            Redis::setex("album_export_progress:{$album_id}", 3600, -1);
            return response()->json([
                'message' => 'Fehler beim Starten des Exports',
                'album_id' => $album_id
                ], 500);
        }
    }
    public function progress(int $album_id)
    {
        $value = Redis::get("album_export_progress:{$album_id}");

        if ($value === null) {
            return response()->json([
                'progress' => 0,
                'status' => 'not_started',
            ]);
        }

        $progress = (int) $value;

        return response()->json([
            'progress' => $progress,
            'status' => match (true) {
                $progress < 0   => 'failed',
                $progress >= 100 => 'done',
                default         => 'processing',
            },
        ]);
    }
    public function downloadZip(int $album_id)
    {
        $album = Album::findOrFail($album_id);

        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Zugriff verweigert'], 403);
        }

        $disk = 'hetzner';
        $remotePath = "albums/{$album_id}/exports/album.zip";

        if (!Storage::disk($disk)->exists($remotePath)) {
            return response()->json(['message' => 'ZIP noch nicht verfügbar'], 404);
        }

        return response()->streamDownload(function () use ($disk, $remotePath) {
            $stream = Storage::disk($disk)->readStream($remotePath);
            if (!$stream) {
                abort(500, "Fehler beim Öffnen der ZIP-Datei.");
            }

            fpassthru($stream);
            fclose($stream);
        }, "album_{$album_id}.zip", [
            'Content-Type' => 'application/zip',
        ]);
    }
}
