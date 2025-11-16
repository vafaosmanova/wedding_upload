<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Models\Video;
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
        $tempDir = storage_path('app/tmp');

        try {
            // --- Получаем список утверждённых медиа ---
            $mediaIds = Redis::smembers("album:{$this->albumId}:approved_media");

            if (empty($mediaIds)) {
                Log::info("No approved media found for album {$this->albumId}.");
                return;
            }

            // --- Создаём временную директорию ---
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
                Log::info("Temporary directory created at: {$tempDir}");
            }

            $zipFile = "{$tempDir}/album_{$this->albumId}.zip";
            $zip = new ZipArchive();

            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("Cannot open ZIP file: {$zipFile}");
            }

            // --- Добавляем файлы в ZIP ---
            $total = count($mediaIds);
            foreach ($mediaIds as $index => $id) {
                $media = Photo::find($id) ?? Video::find($id);
                if (!$media) continue;

                $path = $media->path;
                $filename = $media->filename ?? basename($path);

                if (!Storage::disk($disk)->exists($path)) {
                    Log::warning("File not found on {$disk}: {$path}");
                    continue;
                }

                // Читаем файл как поток (SFTP)
                $stream = Storage::disk($disk)->readStream($path);
                if ($stream) {
                    $zip->addFromString($filename, stream_get_contents($stream));
                    fclose($stream);
                } else {
                    Log::warning("Failed to read file stream: {$path}");
                    continue;
                }

                // Обновляем прогресс каждые 5 файлов или при завершении
                if (($index + 1) % 5 === 0 || $index + 1 === $total) {
                    $progress = intval((($index + 1) / $total) * 100);
                    Redis::set("album_export_progress:{$this->albumId}", $progress);
                }
            }

            $zip->close();
            Log::info("ZIP file successfully created at: {$zipFile}");

            // --- Загружаем ZIP на Hetzner SFTP ---
            $remotePath = "albums/{$this->albumId}/exports/album_{$this->albumId}.zip";
            Storage::disk($disk)->put($remotePath, file_get_contents($zipFile));
            Log::info("ZIP uploaded to Hetzner StorageBox: {$remotePath}");

            // --- Удаляем временный файл ---
            unlink($zipFile);
            Redis::set("album_export_progress:{$this->albumId}", 100);

        } catch (Throwable $e) {
            Redis::set("album_export_progress:{$this->albumId}", -1);
            Log::error("ExportAlbumJob failed for album {$this->albumId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
