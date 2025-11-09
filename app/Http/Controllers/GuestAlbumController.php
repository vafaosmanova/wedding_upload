<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Pin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class GuestAlbumController extends Controller
{
    /** Öffentliche Albumansicht für Gäste (via QR-Code) */
    public function show($albumId)
    {
        $album = Album::with('pin')->findOrFail($albumId);

        return response()->json([
            'id'      => $album->id,
            'title'   => $album->title,
            'qr_code' => $album->qr_code,
            'pin'     => $album->pin->pin ?? null,
        ]);
    }

    /** PIN-Verifizierung und Ausgabe eines temporären Tokens (24 Stunden gültig) */
    public function verifyPin(Request $request, $albumId)
    {
        $request->validate(['pin' => 'required|string']);

        $pin = Pin::where('pin', $request->pin)->first();

        if (!$pin || $pin->album_id != $albumId) {
            return response()->json([
                'success' => false,
                'message' => 'Ungültiger PIN oder Album nicht gefunden.'
            ], 403);
        }

        $token = bin2hex(random_bytes(16));

        try {
            Redis::setex("guest_token:{$token}", 86400, $albumId);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Speichern des Tokens.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'token'   => $token,
        ]);
    }

    /** Abfrage genehmigter Medien für Gäste */
    public function media(Request $request, $albumId)
    {
        $token = $request->header('X-Gast-Token');
        $redisAlbumId = Redis::get("guest_token:{$token}");

        if (!$token || $redisAlbumId != $albumId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $album = Album::findOrFail($albumId);

        $photos = $album->photos()
            ->where('approved', true)
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->filename,
                'url'      => $p->path,
                'type'     => 'image',
            ]);

        $videos = $album->videos()
            ->where('approved', true)
            ->get()
            ->map(fn($v) => [
                'id'       => $v->id,
                'name'     => $v->filename,
                'url'      => $v->path,
                'type'     => 'video',
            ]);

        return response()->json([
            'media' => $photos->concat($videos)->values()
        ]);
    }
}
