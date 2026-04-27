<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GitHubReleasePublisher
{
    /**
     * @return array{tag: string, id: int, html_url: string, name: string, body: string, published_at: string}
     */
    public function publish(string $tag, string $title, ?string $summary): array
    {
        $owner = (string) config('services.github.owner');
        $repo = (string) config('services.github.repo');
        $token = (string) config('services.github.token');
        $defaultBranch = (string) config('services.github.default_branch', 'main');
        $apiVersion = (string) config('services.github.api_version', '2022-11-28');

        if ($owner === '' || $repo === '') {
            throw new \RuntimeException('GitHub repo is not configured (GITHUB_OWNER/GITHUB_REPO).');
        }

        if ($token === '') {
            throw new \RuntimeException('GitHub token is not configured (GITHUB_TOKEN).');
        }

        $normalizedTag = $this->normalizeTag($tag);

        $response = Http::timeout(15)
            ->acceptJson()
            ->withToken($token)
            ->withHeaders([
                'User-Agent' => (string) config('app.name', 'Laravel'),
                'X-GitHub-Api-Version' => $apiVersion,
            ])
            ->post("https://api.github.com/repos/{$owner}/{$repo}/releases", [
                'tag_name' => $normalizedTag,
                'target_commitish' => $defaultBranch,
                'name' => $title,
                'body' => $summary,
                'draft' => false,
                'prerelease' => false,
            ]);

        if (! $response->successful()) {
            $message = $this->extractGitHubErrorMessage($response->json());
            throw new \RuntimeException($message !== '' ? $message : 'GitHub release publish failed.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new \RuntimeException('GitHub release publish returned an invalid response.');
        }

        return [
            'tag' => (string) ($payload['tag_name'] ?? $normalizedTag),
            'id' => (int) ($payload['id'] ?? 0),
            'html_url' => (string) ($payload['html_url'] ?? ''),
            'name' => (string) ($payload['name'] ?? $title),
            'body' => (string) ($payload['body'] ?? ($summary ?? '')),
            'published_at' => (string) ($payload['published_at'] ?? ''),
        ];
    }

    public function suggestNextTag(?string $latestTag): string
    {
        $latestTag = $latestTag !== null ? trim($latestTag) : '';

        if ($latestTag === '') {
            return 'v1.0.0';
        }

        $normalized = ltrim($latestTag, 'v');
        $parts = explode('.', $normalized);

        if (count($parts) !== 3) {
            return 'v1.0.0';
        }

        [$major, $minor, $patch] = $parts;

        if (! ctype_digit($major) || ! ctype_digit($minor) || ! ctype_digit($patch)) {
            return 'v1.0.0';
        }

        $nextPatch = ((int) $patch) + 1;

        return 'v'.(int) $major.'.'.(int) $minor.'.'.$nextPatch;
    }

    private function normalizeTag(string $tag): string
    {
        $tag = trim($tag);

        if ($tag === '') {
            return $tag;
        }

        return str_starts_with($tag, 'v') ? $tag : 'v'.$tag;
    }

    private function extractGitHubErrorMessage(mixed $payload): string
    {
        if (! is_array($payload)) {
            return '';
        }

        $message = (string) ($payload['message'] ?? '');

        if ($message === '') {
            return '';
        }

        if (! isset($payload['errors']) || ! is_array($payload['errors'])) {
            return $message;
        }

        $details = collect($payload['errors'])
            ->filter(fn ($error) => is_array($error) && isset($error['message']))
            ->map(fn ($error) => (string) $error['message'])
            ->filter()
            ->take(3)
            ->implode('; ');

        return $details !== '' ? $message.' ('.$details.')' : $message;
    }
}
