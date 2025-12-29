<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'reddit_url',
        'youtube_url',
        'status',
        'background_path',
        'audio_path',
        'subtitle_path',
        'output_path',
        'script',
        'error_message',
    ];

    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', $this->status ?? 'pending');
    }

    public function getOutputUrlAttribute(): ?string
    {
        if (! $this->output_path) {
            return null;
        }

        $publicRoot = storage_path('app/public/');
        if (str_starts_with($this->output_path, $publicRoot)) {
            $relative = ltrim(str_replace($publicRoot, '', $this->output_path), '/');
            return asset('storage/' . $relative);
        }

        return null;
    }
}
