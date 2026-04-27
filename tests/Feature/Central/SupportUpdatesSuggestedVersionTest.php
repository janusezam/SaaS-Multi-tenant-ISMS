<?php

declare(strict_types=1);

use App\Models\SuperAdmin;
use App\Services\GitHubLatestReleaseService;

test('support & updates page suggests next version instead of hardcoded placeholder', function () {
    app()->instance(GitHubLatestReleaseService::class, new class extends GitHubLatestReleaseService
    {
        public function latest(): ?array
        {
            return [
                'tag' => 'v1.0.4',
                'name' => 'Release v1.0.4',
                'body' => '',
                'html_url' => 'https://example.test/releases/v1.0.4',
                'published_at' => now()->toISOString(),
            ];
        }
    });

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Support Updates Admin',
        'email' => 'support-updates-admin@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.support-updates.index'));

    $response
        ->assertOk()
        ->assertSee('value="v1.0.5"', false)
        ->assertDontSee('v1.2.0');
});
