<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Pin;
use App\Traits\MediaFormatter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Random\RandomException;

class GuestAlbumController extends Controller
{
    use MediaFormatter;

    /** Öffentliche Albumansicht für Gäste (via QR-Code) */
    public function show($album_id)
    {
        $album = Album::with('pin')->findOrFail($album_id);

        return response()->json([
            'id' => $album->id,
            'title' => $album->title,
            'qr_code' => $album->qr_code,
            'pin' => $album->pin->pin ?? null,
        ]);
    }

    /** PIN-Verifizierung und Ausgabe eines temporären Tokens (24 Stunden gültig)
     * @throws RandomException
     */
    public function verifyPin(Request $request, $album_id)
    {
        $request->validate(['pin' => 'required|string']);
        $pin = Pin::where('pin', $request->pin)->first();

        if (!$pin || $pin->album_id != $album_id) {
            return response()->json([
                'success' => false,
                'message' => 'Ungültiger PIN oder Album nicht gefunden.'
            ], 403);
        }

        $token = bin2hex(random_bytes(16));

        try {
            Redis::setex("guest_token:{$token}", 86400, $album_id);
        } catch (Exception) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Speichern des Tokens.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
    /** Zeigt alle genehmigten Medien für Gäste mit gültigem Token */
    public function media(Request $request, $album_id)
    {
        $token = $request->header('X-Gast-Token');
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $redisAlbumId = Redis::get("guest_token:{$token}");
        if (!$redisAlbumId || $redisAlbumId != $album_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $album = Album::findOrFail($album_id);

        $photos = $this->formatMediaCollection(
            $album->photos()->where('approved', true)->get(),
            'image'
        );

        $videos = $this->formatMediaCollection(
            $album->videos()->where('approved', true)->get(),
            'video'
        );

        // Добавляем album_id в каждый объект
        $media = $photos->concat($videos)->map(fn($m) => [
            ...$m->toArray(),
            'album_id' => $album_id,
        ])->values();

        return response()->json(['media' => $media]);
    }

}
