<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Pin;
use Illuminate\Http\Request;
use App\Jobs\ExportAlbumJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
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
            return response()->json([
                'message' => 'Ein Album mit diesem Namen existiert bereits.'
            ], 422);
        }

        $pinExists = Pin::where('pin', $request->pin)
            ->whereHas('album', fn($q) => $q->where('user_id', auth()->id()))
            ->exists();
        if ($pinExists) {
            return response()->json([
                'message' => 'Ein Album mit dieser PIN existiert bereits.'
            ], 422);
        }

        try {
            $album = null;
            $pin = null;

            DB::transaction(function () use ($request, &$album, &$pin) {
                $album = Album::createWithQr([
                    'title' => $request->title,
                    'user_id' => auth()->id(),
                ]);

                $pin = Pin::create([
                    'pin' => $request->pin,
                    'album_id' => $album->id,
                ]);
            });

            return response()->json([
                'album' => $album,
                'qr_code' => $album->qr_code,
                'pin' => $pin->pin,
            ], 201);

        } catch (Throwable $e) {
            Log::error('Error creating album: ' . $e->getMessage());
            return response()->json(['message' => 'Fehler beim Erstellen des Albums'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);
        $request->validate(['title' => 'nullable|string|max:255',
        'pin' => 'nullable|string|min:4|max:10']);
        $album->update($request->only(['title', 'pin']));

        return response()->json(['message' => 'Album aktualisiert',
        'album' => $album]);
    }

    public function destroy($id)
    {
        $album = Album::findOrFail($id);
        $album->delete();
        return response()->json(['message' => 'Album gelöscht']);
    }

    /** Export album to ZIP (owner) */
    public function export($albumId)
    {
        Redis::set("album_export_progress:{$albumId}", 0);
        ExportAlbumJob::dispatch($albumId);
        return response()->json(['message' => 'Export gestartet!']);
    }

    /** Get export progress */
    public function progress($albumId)
    {
        $progress = Redis::get("album_export_progress:{$albumId}") ?? 0;
        return response()->json(['progress' => $progress]);
    }
}
