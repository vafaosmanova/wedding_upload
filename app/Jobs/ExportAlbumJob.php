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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportAlbumJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $albumId;

    public function __construct($albumId)
    {
        $this->albumId = $albumId;
    }

    public function handle()
    {
        $album = Album::with([
            'photos' => fn($q) => $q->where('approved', true),
            'videos' => fn($q) => $q->where('approved', true)
        ])->findOrFail($this->albumId);

        //Für zeitfriestige Speicherung
        $tmpDir = storage_path('app/tmp/exports');
        if (!file_exists($tmpDir)) mkdir($tmpDir, 0755, true);

        $zipFileName = 'album_' . $album->id . '.zip';
        $zipPath = $tmpDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error("ZIP konnte nicht erstellt werden: {$zipPath}");
            return;
        }

        $files = [];
        foreach ($album->photos as $photo) {
            $localPath = $this->downloadFile($photo->path);
            if ($localPath) $files[] = ['path' => $localPath, 'zipPath' => 'photos/' . basename($localPath)];
        }
        foreach ($album->videos as $video) {
            $localPath = $this->downloadFile($video->path);
            if ($localPath) $files[] = ['path' => $localPath, 'zipPath' => 'videos/' . basename($localPath)];
        }

        $totalFiles = count($files);
        if ($totalFiles === 0) {
            Redis::set("album_export_progress:{$album->id}", 100);
            Redis::set("album_export_message:{$album->id}", "Keine Dateien zum Exportieren.");
            return;
        }

        Redis::set("album_export_progress:{$album->id}", 0);
        Redis::set("album_export_progress_counter:{$album->id}", 0);

        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['zipPath']);
            $this->updateProgress($album->id, $totalFiles);
        }

        $zip->close();

        try {
            Storage::disk('hetzner')->putFileAs('exports', new \Illuminate\Http\File($zipPath), $zipFileName);
        } catch (\Exception $e) {
            Log::error("Upload des ZIP fehlgeschlagen: " . $e->getMessage());
        }

        // Clean up local temp files
        foreach ($files as $file) {
            @unlink($file['path']);
        }
        @unlink($zipPath);

        Redis::set("album_export_progress:{$album->id}", 100);
        Redis::del("album_export_progress_counter:{$album->id}");
        Redis::set("album_export_message:{$album->id}", "Export abgeschlossen!");
    }

    protected function downloadFile($remotePath)
    {
        $tmpDir = storage_path('app/tmp/downloads');
        if (!file_exists($tmpDir)) mkdir($tmpDir, 0755, true);

        $localPath = $tmpDir . '/' . basename($remotePath);
        try {
            $contents = Storage::disk('hetzner')->get($remotePath);
            file_put_contents($localPath, $contents);
            return $localPath;
        } catch (\Exception $e) {
            Log::error("Fehler beim Herunterladen: {$remotePath}, {$e->getMessage()}");
            return null;
        }
    }

    protected function updateProgress($albumId, $totalFiles)
    {
        $completed = Redis::incr("album_export_progress_counter:{$albumId}");
        $progress = intval(($completed / $totalFiles) * 100);
        Redis::set("album_export_progress:{$albumId}", min($progress, 100));
    }
}
