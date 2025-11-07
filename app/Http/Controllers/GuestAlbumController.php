<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Pin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class GuestAlbumController extends Controller
{

    public function show($album_id)
    {
        $album = Album::with('pin')->findOrFail($album_id);

        return response()->json([
            'id'    => $album->id,
            'title' => $album->title,
            'qr_code' => $album->qr_code,
            'pin'   => $album->pin->pin ?? null,
        ]);
    }

    // PIN-Überprüfung und Token-Erstellung
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

        // Generiere eindeutigen Token
        $token = Str::uuid()->toString();

        // Speichere Token in Redis für 24h
        Redis::setex("guest_token:$token", 86400, $albumId);

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    // Medien abrufen
    public function media(Request $request, $albumId)
    {
        $token = $request->header('X-Gast-Token');
        if (!$token || Redis::get("guest_token:$token") != $albumId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $album = Album::findOrFail($albumId);
        $photos = $album->photos()->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->filename,
            'url' => asset("storage/photos/{$p->filename}"),
            'type' => 'image',
        ]);
        $videos = $album->videos()->get()->map(fn($v) => [
            'id' => $v->id,
            'name' => $v->filename,
            'url' => asset("storage/videos/{$v->filename}"),
            'type' => 'video',
        ]);

        return response()->json(['media' => $photos->concat($videos)]);
    }

    // Datei hochladen
    public function upload(Request $request, $albumId)
    {
        $token = $request->header('X-Gast-Token');
        if (!$token || Redis::get("guest_token:$token") != $albumId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $album = Album::findOrFail($albumId);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $type = explode('/', $file->getMimeType())[0];
                $filename = time() . '_' . $file->getClientOriginalName();

                if ($type === 'image') {
                    $file->storeAs('public/photos', $filename);
                    $album->photos()->create(['filename' => $filename]);
                } elseif ($type === 'video') {
                    $file->storeAs('public/videos', $filename);
                    $album->videos()->create(['filename' => $filename]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
