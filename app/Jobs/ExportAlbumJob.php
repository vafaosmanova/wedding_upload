<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Throwable;

class ExportAlbumJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected int $albumId;

    public function __construct(int $albumId)
    {
        $this->albumId = $albumId;
    }

    public function handle(): void
    {
        $disk = 'hetzner';
        $redisKey = "album_export_progress:{$this->albumId}";

        $zipPath = sys_get_temp_dir() . "/album_{$this->albumId}.zip";

        try {
            $mediaIds = Redis::smembers("album:{$this->albumId}:approved_media");
            $mediaIds = array_map('intval', $mediaIds);
            if (!$mediaIds) {
                Redis::set($redisKey, 100);
                Log::info("Keine Medien für Album {$this->albumId} gefunden.");
                return;
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("ZIP konnte nicht erstellt werden: {$zipPath}");
            }

            $total = count($mediaIds);
            $added = 0;

            foreach ($mediaIds as $index => $id) {
                $media = Media::find($id);

                if (!$media) continue;

                $path = $media->path;
                $filename = $media->filename ?? basename($path);

                if (!Storage::disk($disk)->exists($path)) {
                    Log::warning("Datei fehlt auf {$disk}: {$path}");
                    continue;
                }

                try {
                    $tmp = tmpfile();
                    $meta = stream_get_meta_data($tmp);
                    $tmpPath = $meta['uri'];

                    $stream = Storage::disk($disk)->readStream($path);
                    if ($stream === false) {
                        throw new \RuntimeException("Fehler beim Lesen: {$path}");
                    }

                    stream_copy_to_stream($stream, $tmp);
                    fclose($stream);

                    if (!$zip->addFile($tmpPath, $filename)) {
                        throw new \RuntimeException("Konnte Datei nicht zum ZIP hinzufügen: {$filename}");
                    }

                    $added++;

                } catch (Throwable $e) {
                    Log::warning("Fehler bei Datei {$path}: " . $e->getMessage());
                }

                if ((($index + 1) % 5 === 0) || ($index + 1 === $total)) {
                    $progress = intval((($index + 1) / $total) * 100);
                    Redis::set($redisKey, $progress);
                }
            }

            $zip->close();

            if ($added === 0) {
                if (file_exists($zipPath)) @unlink($zipPath);
                Redis::set($redisKey, 100);
                Log::warning("Keine Dateien zum ZIP hinzugefügt für Album {$this->albumId}.");
                return;
            }

            $remotePath = "albums/{$this->albumId}/exports/album.zip";

            $uploadStream = fopen($zipPath, 'r');
            Storage::disk($disk)->put($remotePath, $uploadStream);
            fclose($uploadStream);

            Redis::set($redisKey, 100);
            Log::info("ZIP erfolgreich hochgeladen: {$remotePath}");

        } catch (Throwable $e) {
            Redis::set($redisKey, -1);
            Log::error("ExportAlbumJob Fehler für Album {$this->albumId}: " . $e->getMessage());
        } finally {
            if (file_exists($zipPath)) @unlink($zipPath);
        }
    }
}
