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

        $type = 'image';
        $formatted = $this->formatMediaCollectionOwner(
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

    public function approve($id)
    {
        $media = Media::find($id);
        if (!$media) {
            return response()->json(['message' => 'Media not found'], 404);
        }

        $media->approved = true;
        $media->save();

        Redis::sadd("album:{$media->album_id}:approved", $media->id);

        return response()->json(['message' => 'Media approved']);
    }


    /**
     * Löschen
     */
    public function destroy($id)
    {
        $media = Media::find($id);
        if (!$media) {
            return response()->json(['message' => 'Media not found'], 404);
        }

        if (Storage::disk('hetzner')->exists($media->path)) {
            Storage::disk('hetzner')->delete($media->path);
        }

        $media->delete();
        return response()->json(['message' => 'Media deleted']);
    }

    /**
     * Streamen für Besitzer
     */
    public function streamOwnerMedia($media_id)
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
