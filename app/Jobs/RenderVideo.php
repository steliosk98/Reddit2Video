<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\RedditService;
use App\Services\SubtitleService;
use App\Services\TtsService;
use App\Services\VideoRenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RenderVideo implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public array $backoff = [30, 120];

    public function __construct(private readonly int $videoId)
    {
    }

    public function handle(
        RedditService $reddit,
        SubtitleService $subs,
        TtsService $tts,
        VideoRenderService $videoService
    ): void {
        $video = Video::findOrFail($this->videoId);

        try {
            $video->update(['status' => 'fetching_reddit']);
            $thread = $reddit->fetchThread($video->reddit_url);
            $script = $reddit->buildScriptFromThread($thread);
            $video->update(['script' => $script]);

            $video->update(['status' => 'downloading_background']);
            $backgroundPath = storage_path("app/backgrounds/{$video->id}.mp4");
            $videoService->downloadBackground($video->youtube_url, $backgroundPath);
            $video->update(['background_path' => $backgroundPath]);

            $video->update(['status' => 'generating_tts']);
            $audioPath = storage_path("app/audio/{$video->id}.wav");
            $ttsResult = $tts->synthesize($script, $audioPath);
            $video->update(['audio_path' => $audioPath]);

            $video->update(['status' => 'generating_subtitles']);
            $subtitlePath = storage_path("app/subtitles/{$video->id}.ass");
            $subs->generateAssFromScript($script, $ttsResult['timings'], $subtitlePath);
            $video->update(['subtitle_path' => $subtitlePath]);

            $video->update(['status' => 'rendering_video']);
            $outputPath = storage_path("app/public/renders/{$video->id}.mp4");
            $videoService->renderFinalVideo($backgroundPath, $audioPath, $subtitlePath, $outputPath);

            $video->update([
                'status' => 'completed',
                'output_path' => $outputPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('RenderVideo failed', [
                'video_id' => $this->videoId,
                'error' => $e->getMessage(),
            ]);

            $video->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
