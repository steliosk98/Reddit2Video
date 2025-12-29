<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TtsService
{
    public function synthesize(string $text, string $outputPath): array
    {
        $provider = config('services.tts.provider', env('TTS_PROVIDER', 'openai'));

        if ($provider !== 'openai') {
            throw new \RuntimeException("Unsupported TTS provider: {$provider}");
        }

        $apiKey = config('services.openai.key', env('OPENAI_API_KEY'));
        if (! $apiKey) {
            throw new \RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $payload = [
            'model' => 'gpt-4o-mini-tts',
            'voice' => 'alloy',
            'input' => $text,
            'format' => 'wav',
        ];

        $response = Http::timeout(60)
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/audio/speech', $payload);

        $response->throw();

        $audioBinary = $response->body();
        if (! $audioBinary || strlen($audioBinary) === 0) {
            throw new \RuntimeException('TTS provider returned empty audio.');
        }

        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($outputPath, $audioBinary);

        return [
            'audio_path' => $outputPath,
            'timings' => null,
        ];
    }
}
