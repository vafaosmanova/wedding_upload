<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Pin;
use App\Jobs\ExportAlbumJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
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
            'title' => 'required|string|max:255',
            'pin' => 'required|string|min:4|max:10',
        ]);

        $titleExists = Album::where('title', $request->title)
            ->where('user_id', auth()->id())
            ->exists();
        if ($titleExists) {
            return response()->json(['message' => 'Ein Album mit diesem Namen existiert bereits.'], 422);
        }

        $pinExists = Pin::where('pin', $request->pin)
            ->whereHas('album', fn($q) => $q->where('user_id', auth()->id()))
            ->exists();
        if ($pinExists) {
            return response()->json(['message' => 'Ein Album mit dieser PIN existiert bereits.'], 422);
        }

        try {
            $album = Album::createWithQr([
                'title' => $request->title,
                'user_id' => auth()->id(),
            ]);

            Pin::create([
                'pin' => $request->pin,
                'album_id' => $album->id,
            ]);

            return response()->json([
                'album' => $album,
                'qr_code' => $album->qr_code,
                'pin' => $request->pin,
            ], 201);

        } catch (Throwable $e) {
            Log::error("Fehler beim Erstellen des Albums: " . $e->getMessage());
            return response()->json(['message' => 'Fehler beim Erstellen des Albums'], 500);
        }
    }

    public function update(Request $request, $album_id)
    {
        $album = Album::findOrFail($album_id);
        if ($album->user_id !== auth()->id()) {
            $request->validate(['title' => 'nullable|string|max:255', 'pin' => 'nullable|string|min:4|max:10']);
            $album->update($request->only(['title', 'pin']));
        }
        return response()->json(['message' => 'Album aktualisiert', 'album' => $album]);
    }

    public function destroy($album_id)
    {
        $album = Album::findOrFail($album_id);
        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nicht autorisiert'], 403);
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
            return response()->json(['message' => 'Nicht autorisiert'], 403);
        }

        $redisKey = "album_export_progress:{$album_id}";

        try {
            // сброс прогресса
            Redis::set($redisKey, 0);

            // запуск background job
            ExportAlbumJob::dispatch($album_id)->onQueue('exports');

            return response()->json([
                'message' => 'Export gestartet',
                'album_id' => $album_id,
            ]);

        } catch (Throwable $e) {
            // Ошибка! Устанавливаем специальный код состояния "-1"
            Redis::set($redisKey, -1);
            return response()->json(['message' => 'Fehler beim Starten des Exports'], 500);
        }
    }


    public function progress(int $album_id)
    {
        $progress = Redis::get("album_export_progress:{$album_id}");

        if (!is_numeric($progress)) {
            $progress = 0;
        }

        return response()->json(['progress' => (int)$progress]);
    }

    /**
     * Новый endpoint для скачивания ZIP
     * SFTP НЕ умеет раздавать файлы напрямую, поэтому Laravel скачивает.
     */
    public function downloadZip(int $album_id)
    {
        $album = Album::findOrFail($album_id);

        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nicht autorisiert'], 403);
        }

        $path = "albums/{$album_id}/exports/album_{$album_id}.zip";

        if (!Storage::disk('hetzner')->exists($path)) {
            return response()->json(['message' => 'ZIP noch nicht verfügbar'], 404);
        }

        // читаем файл через SFTP
        $binary = Storage::disk('hetzner')->get($path);

        return response($binary, 200, [
            'Content-Type' => 'application/zip',
            'Content-Length' => strlen($binary),
            'Content-Disposition' => "attachment; filename=album_{$album_id}.zip",
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

}
