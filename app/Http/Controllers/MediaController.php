<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Photo;
use App\Models\Video;
use Illuminate\Support\Facades\Redis;

class MediaController extends Controller
{
    /** Alle Medien eines Albums (öffentliche/gestattete Ansicht) */
    public function index($albumId)
    {
        $photos = Photo::where('album_id', $albumId)->get();
        $videos = Video::where('album_id', $albumId)->get();

        return response()->json([
            'photos' => $photos,
            'videos' => $videos,
        ]);
    }

    /** Nur Besitzer: Liste der noch nicht genehmigten Medien (für MediaApproval.vue) */
    public function pending()
    {
        // Sicherstellen, dass angemeldeter Benutzer existiert
        if (!auth()->check()) {
            return response()->json(['message' => 'Nicht autorisiert'], 403);
        }

        $userId = auth()->id();

        // Fotos und Videos, deren Album dem Besitzer gehört und die nicht genehmigt sind
        $photos = Photo::whereHas('album', fn($q) => $q->where('user_id', $userId))
            ->where('approved', false)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->filename,
                'album_id' => $p->album_id,
                'type' => 'photo',
                'path' => $p->path,
            ]);

        $videos = Video::whereHas('album', fn($q) => $q->where('user_id', $userId))
            ->where('approved', false)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->filename,
                'album_id' => $v->album_id,
                'type' => 'video',
                'path' => $v->path,
            ]);

        return response()->json(array_merge($photos->toArray(), $videos->toArray()));
    }

    /** Einzelnes Medium genehmigen (Besitzer) */
    public function approve(Request $request, $mediaId)
    {
        $request->validate(['type' => 'required|in:photo,video']);

        if ($request->type === 'photo') {
            $media = Photo::findOrFail($mediaId);
        } else {
            $media = Video::findOrFail($mediaId);
        }

        // Nur Album-Besitzer darf genehmigen
        if ($media->album->user_id !== auth()->id()) {
            return response()->json(['message' => 'Nicht autorisiert'], 403);
        }

        $media->approved = true;
        $media->save();

        return response()->json(['message' => 'Medium genehmigt.']);
    }

    /** Gast-/Besitzer-Export-Status-Endpoint (если вызывается через /api/media/export-status?guest_token=...) — необязательно */
    public function exportStatus(Request $request)
    {
        $token = $request->header('X-Gast-Token');
        if ($token) {
            $progress = Redis::get("album_export_progress:guest:{$token}") ?? 0;
            return response()->json(['progress' => (int)$progress]);
        }

        $albumId = $request->query('album_id');
        if ($albumId) {
            $progress = Redis::get("album_export_progress:{$albumId}") ?? 0;
            return response()->json(['progress' => (int)$progress]);
        }

        return response()->json(['progress' => 0]);
    }
}
