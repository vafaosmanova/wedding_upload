<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Throwable;

class ExportAlbumJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    protected int $albumId;

    public function __construct(int $albumId)
    {
        $this->albumId = $albumId;
    }

    public function handle(): void
    {
        $disk = 'hetzner';
        $redisKey = "album_export_progress:{$this->albumId}";

        try {
            Redis::setex($redisKey, 3600, 0);

            $mediaIds = Redis::smembers("album:{$this->albumId}:approved_media");
            $mediaIds = array_map('intval', $mediaIds);

            if (empty($mediaIds)) {
                Redis::setex($redisKey, 3600, 100);
                Log::info("Keine freigegebenen Medien für Album {$this->albumId}.");
                return;
            }

            $mediaCollection = Media::whereIn('id', $mediaIds)
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            if ($mediaCollection->isEmpty()) {
                Log::warning("Media-Datensätze fehlen für Album {$this->albumId}.");
                return;
            }


            $tmpPath = sys_get_temp_dir(). "/album_{$this->albumId}." . uniqid(). "zip";

            $zip = new ZipArchive();
            if ($zip->open($tmpPath, ZipArchive::CREATE) !== true) {
                throw new \RuntimeException('ZIP-Archiv konnte nicht geöffnet werden.');
            }

            $total = count($mediaIds);
            $processed = 0;

            foreach ($mediaIds as $index => $mediaId) {
                if (!isset($mediaCollection[$mediaId])) {
                    continue;
                }

                $media = $mediaCollection[$mediaId];
                $path = $media->path;
                $filename = $media->filename ?: basename($path);

                if (!Storage::disk($disk)->exists($path)) {
                    Log::warning("Datei fehlt im Storage: {$path}");
                    continue;
                }

                try {
                    $content = Storage::disk($disk)->get($path);
                    $zip->addFromString($filename, $content);
                    $processed++;
                } catch (Throwable $e) {
                    Log::warning("Fehler beim Hinzufügen von {$path}: {$e->getMessage()}");
                }

                if ((($index + 1) % 5 === 0) || ($index + 1 === $total)) {
                    $progress = (int) floor((($index + 1) / $total) * 100);
                    Redis::setex($redisKey, 3600, min($progress, 99));
                }
            }

            $zip->close();

            if ($processed === 0) {
                @unlink($tmpPath);
                Redis::setex($redisKey, 3600, 100);
                Log::warning("ZIP enthält keine Dateien für Album {$this->albumId}.");
                return;
            }

            $remotePath = "albums/{$this->albumId}/exports/album.zip";
            Storage::disk($disk)->put($remotePath, fopen($tmpPath, 'r'));
            Redis::setex($redisKey, 3600, 100);
            @unlink($tmpPath);

            Redis::setex($redisKey, 3600, 100);
            Log::info("Album {$this->albumId} erfolgreich als ZIP exportiert.");

        } catch (Throwable $e) {
            Redis::setex($redisKey, 3600, -1);
            Log::error("ExportAlbumJob Fehler (Album {$this->albumId}): {$e->getMessage()}");
        }
    }
}
