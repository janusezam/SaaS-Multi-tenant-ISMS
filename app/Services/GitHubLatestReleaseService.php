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
        return Cache::store('central')->remember('github.latest_release', now()->addSeconds(30), function (): ?array {
            $owner = (string) config('services.github.owner');
            $repo = (string) config('services.github.repo');
            $token = (string) config('services.github.token');
            $apiVersion = (string) config('services.github.api_version', '2022-11-28');

            $request = Http::timeout(8)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => (string) config('app.name', 'Laravel'),
                    'X-GitHub-Api-Version' => $apiVersion,
                ]);

            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request->get("https://api.github.com/repos/{$owner}/{$repo}/releases/latest");

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
