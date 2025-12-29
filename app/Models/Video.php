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
}
