<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RedditService
{
    public function fetchThread(string $redditUrl): array
    {
        $url = rtrim($redditUrl, '/') . '/.json';

        $response = Http::timeout(15)->get($url);
        $response->throw();

        $payload = $response->json();
        if (! is_array($payload) || count($payload) < 2) {
            throw new \RuntimeException('Unexpected Reddit response format.');
        }

        $post = $payload[0]['data']['children'][0]['data'] ?? [];
        $comments = $payload[1]['data']['children'] ?? [];

        $filteredComments = [];
        foreach ($comments as $comment) {
            $data = $comment['data'] ?? [];
            $body = trim((string) ($data['body'] ?? ''));

            if ($body === '' || in_array($body, ['[deleted]', '[removed]'], true)) {
                continue;
            }

            if (mb_strlen($body) < 5) {
                continue;
            }

            $filteredComments[] = [
                'author' => (string) ($data['author'] ?? 'unknown'),
                'body' => $body,
                'score' => (int) ($data['score'] ?? 0),
            ];
        }

        return [
            'title' => (string) ($post['title'] ?? ''),
            'selftext' => (string) ($post['selftext'] ?? ''),
            'comments' => array_slice($filteredComments, 0, 15),
        ];
    }

    public function buildScriptFromThread(array $threadData): string
    {
        $title = trim((string) ($threadData['title'] ?? ''));
        $selftext = trim((string) ($threadData['selftext'] ?? ''));
        $comments = $threadData['comments'] ?? [];

        $parts = array_filter([$title, $selftext]);
        if (! empty($comments)) {
            $parts[] = 'Top comments:';
            foreach ($comments as $comment) {
                $parts[] = sprintf('Comment by %s: %s', $comment['author'], $comment['body']);
            }
        }

        $script = trim(implode("\n\n", $parts));
        return mb_substr($script, 0, 5000);
    }
}
