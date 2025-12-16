<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Media;
use App\Models\Pin;
use App\Traits\MediaFormatter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Random\RandomException;

class GuestAlbumController extends Controller
{
    use MediaFormatter;

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

    /**
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

    public function media(Request $request, $album_id)
    {
        $token = $request->header('Guest-Token');
        $redisAlbumId = Redis::get("guest_token:{$token}");
        if (!$token || !$redisAlbumId || $redisAlbumId != $album_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $media = Media::where('album_id', $album_id)
            ->get();

        $type = 'image';
        $formatted = $this->formatMediaCollectionGuest(
            $media->map(function($item) use (&$type) {
                $type = Str::startsWith($item->mime_type, 'video/') ? 'video' : 'image';
                return $item;
            }),
            $type
        );

        return response()->json(['media' => $formatted]);
        }
    public function streamMedia(Request $request, $album_id, $media_id)
    {
        $token = $request->header('Guest-Token');
        $redisAlbumId = Redis::get("guest_token:{$token}");
        if (!$token || !$redisAlbumId || $redisAlbumId != $album_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $media = Media::findOrFail($media_id);

        if (!Storage::disk('hetzner')->exists($media->path)) {
            abort(404, 'File not found');
        }

        $file = Storage::disk('hetzner')->response($media->path);
        return response($file, 200)->header('Content-Type', $media->mime_type)
            ->header('Content-Disposition', 'inline');
    }
    public function downloadZip(Request $request, int $album_id)
    {
        $token = $request->header('Guest-Token');
        $redisAlbumId = Redis::get("guest_token:{$token}");

        if (!$token || !$redisAlbumId || $redisAlbumId != $album_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $disk = 'hetzner';
        $zipPath = "albums/{$album_id}/exports/album.zip";

        if (!Storage::disk($disk)->exists($zipPath)) {
            return response()->json(['message' => 'ZIP not generated'], 404);
        }

        return Storage::disk($disk)->download($zipPath, "album_{$album_id}.zip");
    }

}
