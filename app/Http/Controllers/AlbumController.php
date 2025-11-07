<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Pin;
use Illuminate\Http\Request;
use App\Jobs\ExportAlbumJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AlbumController extends Controller
{
    /** List all albums for authenticated owner */
    public function index()
    {
        $albums = Album::where('user_id', auth()->user()->id)->get();
        return response()->json($albums);
    }

    /** Create a new album (owner) */
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

    /** Update album (owner) */
    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);
        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nicht autorisiert'], 403);
        }
        $request->validate(['title' => 'required|string|max:255']);
        $album->update(['title' => $request->title]);

        return response()->json($album);
    }

    /** Delete album (owner) */
    public function destroy($id)
    {
        $album = Album::findOrFail($id);
        if ($album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nicht autorisiert'], 403);
        }
        $album->delete();
        return response()->json(['message' => 'Album gelöscht']);
    }

    /** Guest: verify PIN and create temporary Redis token */
    public function verifyPin(Request $request, $id)
    {
        $request->validate(['pin' => 'required|string']);

        $pin = Pin::where('pin', $request->input('pin'))->first();

        if (!$pin || $pin->album_id != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Ungültiger PIN oder Album nicht gefunden.'
            ], 403);
        }

        $album = Album::find($id);
        if (!$album) {
            return response()->json([
                'success' => false,
                'message' => 'Album nicht gefunden.'
            ], 404);
        }

        // Создаём Redis токен для гостя (10 минут)
        $token = bin2hex(random_bytes(16));
        Redis::setex("guest_access:{$token}", 600, $id);

        return response()->json([
            'success' => true,
            'message' => 'PIN erfolgreich überprüft.',
            'album' => $album,
            'token' => $token
        ]);
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
