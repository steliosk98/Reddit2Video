<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Video Status</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <main class="max-w-3xl mx-auto px-6 py-16 space-y-8">
        <a href="{{ route('home') }}" class="text-sm text-emerald-300">&larr; Back</a>

        <div class="space-y-3">
            <h1 class="text-3xl font-semibold tracking-tight">Video Status</h1>
            <p class="text-slate-300">Status: <span class="font-semibold">{{ $video->status_label }}</span></p>
            @if($video->script)
                <p class="text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($video->script, 220) }}</p>
            @endif
        </div>

        @if($video->status === 'completed' && $video->output_url)
            <div class="space-y-4">
                <video controls class="w-full rounded-lg">
                    <source src="{{ $video->output_url }}" type="video/mp4">
                </video>
                <a
                    href="{{ $video->output_url }}"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-400 px-5 py-2 font-semibold text-slate-900"
                    download
                >
                    Download Video
                </a>
            </div>
        @elseif($video->status === 'failed')
            <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-red-200">
                <p class="font-semibold">Render failed</p>
                <p class="text-sm">{{ $video->error_message }}</p>
            </div>
        @else
            <div class="rounded-lg border border-slate-800 bg-slate-900/60 p-4 text-slate-300">
                Rendering in progress. Refresh this page to see updates.
            </div>
        @endif
    </main>
</body>
</html>
