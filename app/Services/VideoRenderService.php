<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class VideoRenderService
{
    public function downloadBackground(string $youtubeUrl, string $outputPath): string
    {
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ytDlp = env('YTDLP_PATH', 'yt-dlp');
        $process = new Process([
            $ytDlp,
            '-f',
            'bestvideo+bestaudio/best',
            '-o',
            $outputPath,
            $youtubeUrl,
        ]);
        $process->setTimeout(300);
        $process->mustRun();

        return $outputPath;
    }

    public function renderFinalVideo(
        string $backgroundPath,
        string $audioPath,
        string $subtitlePath,
        string $outputPath
    ): string {
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');

        $filter = "scale=1080:1920:force_original_aspect_ratio=increase,";
        $filter .= "crop=1080:1920,subtitles={$subtitlePath}";

        $process = new Process([
            $ffmpeg,
            '-y',
            '-i', $backgroundPath,
            '-i', $audioPath,
            '-vf', $filter,
            '-map', '0:v',
            '-map', '1:a',
            '-c:v', 'libx264',
            '-c:a', 'aac',
            '-shortest',
            $outputPath,
        ]);
        $process->setTimeout(600);
        $process->mustRun();

        return $outputPath;
    }
}
