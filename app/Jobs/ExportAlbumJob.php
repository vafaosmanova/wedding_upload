<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Album;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ZipArchive;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ExportAlbumJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $albumId;

    /**
     * Create a new job instance.
     */
    public function __construct($albumId)
    {
        $this->albumId = $albumId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $album = Album::with('photos', 'videos')->findOrFail($this->albumId);

        // Ordner für Exporte prüfen
        $exportDir = storage_path('app/public/exports');
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zipFileName = 'exports/album_' . $album->id . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error("ZIP konnte nicht erstellt werden: {$zipPath}");
            return;
        }

        // Alle vorhandenen Dateien sammeln
        $files = [];
        foreach ($album->photos as $photo) {
            $filePath = storage_path('app/public/' . $photo->path);
            if (file_exists($filePath)) {
                $files[] = $filePath;
            }
        }

        foreach ($album->videos as $video) {
            $filePath = storage_path('app/public/' . $video->path);
            if (file_exists($filePath)) {
                $files[] = $filePath;
            }
        }

        $totalFiles = count($files);

        if ($totalFiles === 0) {
            Redis::set("album_export_progress:{$album->id}", 100);
            Redis::set("album_export_message:{$album->id}", "Keine Dateien zum Exportieren.");
            return;
        }

        // Fortschritt initialisieren
        Redis::set("album_export_progress:{$album->id}", 0);
        Redis::set("album_export_progress_counter:{$album->id}", 0);

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
            $this->updateProgress($album->id, $totalFiles);
        }

        $zip->close();

        // Fortschritt abschließen
        Redis::set("album_export_progress:{$album->id}", 100);
        Redis::del("album_export_progress_counter:{$album->id}");
        Redis::set("album_export_message:{$album->id}", "Export abgeschlossen!");
    }

    /**
     * Fortschritt in Redis aktualisieren.
     */
    protected function updateProgress($albumId, $totalFiles)
    {
        $completed = Redis::incr("album_export_progress_counter:{$albumId}");
        $progress = intval(($completed / $totalFiles) * 100);
        Redis::set("album_export_progress:{$albumId}", min($progress, 100));
    }
}
