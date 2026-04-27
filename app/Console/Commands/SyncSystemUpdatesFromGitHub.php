<?php

namespace App\Console\Commands;

use App\Models\SystemUpdate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncSystemUpdatesFromGitHub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-system-updates-from-github {--limit=30 : Max releases to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync GitHub Releases into tenant-facing system updates (central table).';

    public function handle(): int
    {
        $owner = (string) config('services.github.owner');
        $repo = (string) config('services.github.repo');
        $token = (string) config('services.github.token');
        $apiVersion = (string) config('services.github.api_version', '2022-11-28');
        $limit = max(1, min(100, (int) $this->option('limit')));

        if ($owner === '' || $repo === '') {
            $this->error('GitHub repo is not configured. Set GITHUB_OWNER and GITHUB_REPO.');

            return self::FAILURE;
        }

        $request = Http::timeout(15)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => (string) config('app.name', 'Laravel'),
                'X-GitHub-Api-Version' => $apiVersion,
            ]);

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->get("https://api.github.com/repos/{$owner}/{$repo}/releases", [
            'per_page' => $limit,
        ]);

        if (! $response->successful()) {
            $this->error('GitHub sync failed: '.$response->status().' '.$response->body());

            return self::FAILURE;
        }

        $releases = $response->json();

        if (! is_array($releases)) {
            $this->error('GitHub sync failed: invalid response payload.');

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;

        foreach ($releases as $release) {
            if (! is_array($release)) {
                continue;
            }

            if (($release['draft'] ?? false) === true) {
                continue;
            }

            $tag = trim((string) ($release['tag_name'] ?? ''));

            if ($tag === '') {
                continue;
            }

            $name = trim((string) ($release['name'] ?? ''));
            $body = (string) ($release['body'] ?? '');
            $htmlUrl = (string) ($release['html_url'] ?? '');
            $publishedAt = (string) ($release['published_at'] ?? '');

            $title = $name !== '' ? $name : "Release {$tag}";

            $attributes = [
                'title' => $title,
                'summary' => $body !== '' ? $body : null,
                'source' => 'github',
                'is_published' => true,
                'published_at' => $publishedAt !== '' ? $publishedAt : now(),
                'meta' => [
                    'github' => [
                        'release_id' => (int) ($release['id'] ?? 0),
                        'html_url' => $htmlUrl,
                        'name' => $name,
                        'published_at' => $publishedAt,
                    ],
                ],
            ];

            $existing = SystemUpdate::query()->where('version', $tag)->first();

            if ($existing === null) {
                SystemUpdate::query()->create(array_merge($attributes, [
                    'version' => $tag,
                ]));
                $created++;

                continue;
            }

            $existing->fill($attributes);
            $existing->save();
            $updated++;
        }

        $this->info("GitHub releases synced. Created: {$created}, Updated: {$updated}.");

        return self::SUCCESS;
    }
}
