<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\MediaFormatter;

class MediaController extends Controller
{
    use MediaFormatter;
    /**
     * Medien in Genehmigungswarteschlange
     */
    public function pending($album_id)
    {
        $media = Media::where('album_id', $album_id)
            ->where('approved', false)
            ->get();

        $type = '';
        $formatted = $this->formatMediaCollectionUser(
            $media->map(function ($item) use (&$type) {
                $type = Str::startsWith($item->mime_type, 'video/') ? 'video' : 'image';
                return $item;
            }),
            $type
        );

        return response()->json(['media' => $formatted]);
    }

    /**
     * Genehmigen
     */

    public function approve($media_id)
    {
        $media = Media::find($media_id);
        if (!$media) {
            return response()->json(['message' => 'Media not found'], 404);
        }

        $media->approved = true;
        $media->save();

        Redis::sadd("album:{$media->album_id}:approved_media", $media->id);

        return response()->json([
            'message' => 'Media approved',
            'media_id' => $media->id
        ], 200);
    }


    /**
     * Löschen
     */
    public function destroy($media_id)
    {
        $media = Media::find($media_id);
        if (!$media) {
            return response()->json(['message' => 'Media not found'], 404);
        }

        if (Storage::disk('hetzner')->exists($media->path)) {
            Storage::disk('hetzner')->delete($media->path);
        }

        $media->delete();
        Redis::srem("album:{$media->album_id}:approved_media", $media->id);
        return response()->json(['message' => 'Media deleted']);
    }

    /**
     * Streamen für User
     */
    public function streamUserMedia($media_id)
    {
        $media = Media::findOrFail($media_id);

        if (!Storage::disk('hetzner')->exists($media->path)) {
            abort(404, 'File not found');
        }

        $file = Storage::disk('hetzner')->get($media->path);
        return response($file, 200)->header('Content-Type', $media->mime_type)
            ->header('Content-Disposition', 'inline');
    }
}
