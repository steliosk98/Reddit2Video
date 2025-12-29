<?php

namespace App\Services;

class SubtitleService
{
    public function generateAssFromScript(string $script, ?array $timings, string $outputPath): string
    {
        $header = "[Script Info]\n";
        $header .= "ScriptType: v4.00+\n";
        $header .= "PlayResX: 1080\n";
        $header .= "PlayResY: 1920\n";
        $header .= "\n[V4+ Styles]\n";
        $header .= "Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding\n";
        $header .= "Style: Default,Arial,54,&H00FFFFFF,&H0000FFFF,&H00000000,&H64000000,0,0,0,0,100,100,0,0,1,3,0,2,40,40,120,1\n";
        $header .= "\n[Events]\n";
        $header .= "Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text\n";

        $events = '';

        if ($timings && count($timings) > 0) {
            $events .= $this->buildKaraokeEvents($timings);
        } else {
            $events .= $this->buildEstimatedEvents($script);
        }

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($outputPath, $header . $events);

        return $outputPath;
    }

    private function buildKaraokeEvents(array $timings): string
    {
        $start = $timings[0]['start'] ?? 0.0;
        $end = $timings[count($timings) - 1]['end'] ?? ($start + 1.0);

        $karaokeText = '';
        foreach ($timings as $item) {
            $word = trim((string) ($item['word'] ?? ''));
            if ($word === '') {
                continue;
            }

            $duration = max(1, (int) round((float) ($item['end'] ?? 0) * 100 - (float) ($item['start'] ?? 0) * 100));
            $karaokeText .= sprintf('{\\k%d}%s ', $duration, $this->escapeAss($word));
        }

        $line = sprintf(
            "Dialogue: 0,%s,%s,Default,,0,0,0,,%s\n",
            $this->toAssTime($start),
            $this->toAssTime($end),
            trim($karaokeText)
        );

        return $line;
    }

    private function buildEstimatedEvents(string $script): string
    {
        $words = preg_split('/\s+/', trim($script)) ?: [];
        $events = '';

        $wordsPerLine = 8;
        $wordDuration = 0.4;
        $cursor = 0.0;

        for ($i = 0; $i < count($words); $i += $wordsPerLine) {
            $chunk = array_slice($words, $i, $wordsPerLine);
            $text = implode(' ', array_map([$this, 'escapeAss'], $chunk));
            $duration = count($chunk) * $wordDuration;

            $start = $cursor;
            $end = $cursor + $duration;
            $cursor = $end;

            $events .= sprintf(
                "Dialogue: 0,%s,%s,Default,,0,0,0,,%s\n",
                $this->toAssTime($start),
                $this->toAssTime($end),
                $text
            );
        }

        return $events;
    }

    private function toAssTime(float $seconds): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%d:%02d:%05.2f', $hours, $minutes, $secs);
    }

    private function escapeAss(string $text): string
    {
        return str_replace(['{', '}', '\\'], ['(', ')', '\\\\'], $text);
    }
}
