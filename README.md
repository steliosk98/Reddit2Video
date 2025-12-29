# Reddit2Video

Reddit2Video is a Laravel web app that turns a Reddit thread into a short-form
video (TikTok/Shorts style) with TTS narration, karaoke-style subtitles, and a
user-supplied YouTube background video rendered via FFmpeg.

## Features (MVP)

- Paste a Reddit thread URL and a YouTube URL.
- Fetch post + top comments and build a narration script.
- Generate TTS audio (OpenAI TTS by default).
- Create karaoke-style `.ass` subtitles.
- Download background video with `yt-dlp`.
- Render final MP4 with FFmpeg in a queued job.

## Tech Stack

- PHP 8.1+ / Laravel 11
- Queue worker (database or Redis)
- FFmpeg + yt-dlp
- OpenAI TTS (pluggable provider)

## Requirements

- PHP 8.1+
- Composer
- FFmpeg (CLI)
- yt-dlp (CLI)
- Node.js (only if you plan to build frontend assets)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

## Environment Variables

Add these to `.env` as needed:

```
OPENAI_API_KEY=your-key-here
TTS_PROVIDER=openai
FFMPEG_PATH=/usr/bin/ffmpeg
YTDLP_PATH=/usr/local/bin/yt-dlp
QUEUE_CONNECTION=database
```

## Running Locally

```bash
php artisan serve
```

In a separate terminal, start the queue worker:

```bash
php artisan queue:work
```

## Notes

- The render pipeline runs in a queue job and can take time depending on the
  length of the Reddit thread and background video.
- Make sure `ffmpeg` and `yt-dlp` are accessible at the paths configured in
  `.env`.

## Project Structure (High Level)

```
app/
  Http/Controllers/
  Jobs/
  Models/
  Services/
resources/views/
routes/web.php
```

## License

MIT
