<?php

namespace App\Http\Controllers;

use App\Jobs\RenderVideo;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reddit_url' => ['required', 'url', 'regex:/^https:\/\/(www\.)?reddit\.com\//i'],
            'youtube_url' => ['required', 'url', 'regex:/^https:\/\/(www\.)?(youtube\.com|youtu\.be)\//i'],
        ]);

        $video = Video::create([
            'reddit_url' => $validated['reddit_url'],
            'youtube_url' => $validated['youtube_url'],
            'status' => 'pending',
        ]);

        RenderVideo::dispatch($video->id);

        return redirect()->route('videos.show', $video);
    }

    public function show(Video $video): View
    {
        return view('videos.show', [
            'video' => $video,
        ]);
    }
}
