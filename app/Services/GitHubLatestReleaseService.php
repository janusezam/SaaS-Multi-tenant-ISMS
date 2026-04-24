<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GitHubLatestReleaseService
{
    /**
     * @return array{tag: string, name: string, body: string, html_url: string, published_at: string}|null
     */
    public function latest(): ?array
    {
        return Cache::remember('github.latest_release', now()->addMinutes(10), function (): ?array {
            $response = Http::timeout(8)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => (string) config('app.name', 'Laravel'),
                    'X-GitHub-Api-Version' => '2022-11-28',
                ])
                ->get('https://api.github.com/repos/janusezam/SaaS-Multi-tenant-ISMS/releases/latest');

            if (! $response->successful()) {
                return null;
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                return null;
            }

            return [
                'tag' => (string) ($payload['tag_name'] ?? ''),
                'name' => (string) ($payload['name'] ?? ''),
                'body' => (string) ($payload['body'] ?? ''),
                'html_url' => (string) ($payload['html_url'] ?? ''),
                'published_at' => (string) ($payload['published_at'] ?? ''),
            ];
        });
    }
}
