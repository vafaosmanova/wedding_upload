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
        // Используем стандартный путь для временных файлов Laravel
        $tempDir = 'temp_exports';

        $redisKey = "album_export_progress:{$this->albumId}";
        $zipFile = null; // Инициализируем переменную для finally блока

        try {
            // --- Sicherstellen, dass temporäres Verzeichnis existiert ---
            if (!Storage::disk('local')->exists($tempDir)) {
                Storage::disk('local')->makeDirectory($tempDir);
                Log::info("Temporäres Verzeichnis erstellt: storage/app/{$tempDir}");
            }

            // --- Medien-IDs aus Redis (oder andere Quelle) lesen ---
            $mediaIds = Redis::smembers("album:{$this->albumId}:approved_media");
            if (empty($mediaIds)) {
                Log::info("Keine freigegebenen Medien für Album {$this->albumId} gefunden.");
                Redis::set($redisKey, 100);
                return;
            }

            // Zip file location within storage/app
            $zipFilename = "album_{$this->albumId}_" . time() . ".zip";
            $zipFile = storage_path('app/' . $tempDir . '/' . $zipFilename);

            $zip = new ZipArchive();

            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("ZIP-Datei konnte nicht geöffnet werden: {$zipFile}");
            }

            $total = count($mediaIds);
            $added = 0;

            foreach ($mediaIds as $index => $id) {
                $media = Media::find($id);

                if (!$media) {
                    Log::warning("Medium mit ID {$id} nicht in Photo/Video gefunden. Überspringe.");
                    continue;
                }

                $path = $media->path;
                $filename = $media->filename ?? basename($path);

                // Проверка существования на удаленном диске перед копированием
                if (!Storage::disk($disk)->exists($path)) {
                    Log::warning("Файл не найден на диске {$disk}: {$path}. Пропускаю.");
                    continue;
                }

                $localTempMediaFilename = "media_temp_{$id}_" . uniqid();
                $localTempMediaFullPath = storage_path('app/' . $localTempMediaFilename);

                try {
                    // --- ИСПРАВЛЕНИЕ OOM: Скачиваем удаленный файл локально ---
                    // Using Laravel Storage disk put/get streams the content internally
                    $contents = Storage::disk($disk)->get($path);
                    Storage::disk('local')->put($localTempMediaFilename, $contents);
                    unset($contents); // Очищаем память сразу

                    // --- ИСПРАВЛЕНИЕ OOM: Добавляем локальный файл в ZIP напрямую ---
                    if ($zip->addFile($localTempMediaFullPath, $filename) === false) {
                        throw new \RuntimeException("Не удалось добавить файл в ZIP: {$filename}");
                    }
                    $added++;

                } catch (Throwable $e) {
                    Log::warning("Ошибка при обработке файла ({$path}): " . $e->getMessage());
                } finally {
                    // Убедимся, что временный медиа-файл удален в любом случае
                    if (file_exists($localTempMediaFullPath)) {
                        @unlink($localTempMediaFullPath);
                    }
                }

                // Обновление прогресса
                if ((($index + 1) % 5 === 0) || ($index + 1 === $total)) {
                    $progress = intval((($index + 1) / $total) * 100);
                    Redis::set($redisKey, $progress);
                }
            }

            $zip->close(); // Закрываем ZIP-архив

            if ($added === 0) {
                Log::warning("Нет файлов для добавления в ZIP для Album {$this->albumId}. Удаляю пустой архив.");
                if (file_exists($zipFile)) { @unlink($zipFile); }
                Redis::set($redisKey, 100);
                return;
            }

            // --- Загрузка ZIP на Hetzner SFTP ---
            $remotePath = "albums/{$this->albumId}/exports/album_{$this->albumId}.zip";

            // Используем stream для загрузки локального ZIP на удаленный диск
            $uploadStream = fopen($zipFile, 'r');
            if ($uploadStream === false) {
                throw new \RuntimeException("Не удалось открыть ZIP-файл для загрузки: {$zipFile}");
            }

            Storage::disk($disk)->put($remotePath, $uploadStream);

            if (is_resource($uploadStream)) {
                fclose($uploadStream);
            }

            Log::info("ZIP успешно загружен в: {$remotePath}");
            Redis::set($redisKey, 100);

        } catch (Throwable $e) {
            Redis::set($redisKey, -1);
            Log::error("ExportAlbumJob завершился с ошибкой для Album {$this->albumId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            // Финальная очистка: удаляем главный временный ZIP-файл
            if (isset($zipFile) && file_exists($zipFile)) {
                @unlink($zipFile);
            }
        }
    }
}
