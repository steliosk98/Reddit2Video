<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reddit2Video</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <main class="max-w-2xl mx-auto px-6 py-16">
        <h1 class="text-4xl font-semibold tracking-tight">Reddit2Video</h1>
        <p class="mt-4 text-slate-300">
            Turn a Reddit thread into a short-form video with narration, subtitles,
            and a YouTube background clip.
        </p>

        <form method="POST" action="{{ route('videos.store') }}" class="mt-8 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-200" for="reddit_url">Reddit URL</label>
                <input
                    id="reddit_url"
                    name="reddit_url"
                    type="url"
                    value="{{ old('reddit_url') }}"
                    placeholder="https://www.reddit.com/r/AskReddit/comments/..."
                    class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100"
                    required
                />
                @error('reddit_url')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-200" for="youtube_url">YouTube URL</label>
                <input
                    id="youtube_url"
                    name="youtube_url"
                    type="url"
                    value="{{ old('youtube_url') }}"
                    placeholder="https://www.youtube.com/watch?v=..."
                    class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100"
                    required
                />
                @error('youtube_url')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-400 px-6 py-3 font-semibold text-slate-900 transition hover:bg-emerald-300"
            >
                Create Video
            </button>
        </form>
    </main>
</body>
</html>
