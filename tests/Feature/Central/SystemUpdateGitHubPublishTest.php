<?php

use App\Models\SuperAdmin;
use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Http;

test('central can publish a system update that creates a GitHub release when source is github', function () {
    config()->set('services.github.owner', 'janusezam');
    config()->set('services.github.repo', 'SaaS-Multi-tenant-ISMS');
    config()->set('services.github.default_branch', 'main');
    config()->set('services.github.token', 'test-token');

    Http::fake(function ($request) {
        $url = (string) $request->url();

        if (str_ends_with($url, '/releases/latest')) {
            return Http::response([
                'tag_name' => 'v1.0.4',
                'name' => 'Release v1.0.4',
                'body' => 'Previous release',
                'html_url' => 'https://github.com/janusezam/SaaS-Multi-tenant-ISMS/releases/tag/v1.0.4',
                'published_at' => '2026-04-27T00:00:00Z',
            ], 200);
        }

        if (str_ends_with($url, '/releases') && $request->method() === 'POST') {
            expect($request['tag_name'])->toBe('v1.0.5');
            expect($request['target_commitish'])->toBe('main');
            expect($request['name'])->toBe('My New Release');
            expect($request['body'])->toBe('Hello release notes');

            return Http::response([
                'id' => 123,
                'tag_name' => 'v1.0.5',
                'name' => 'My New Release',
                'body' => 'Hello release notes',
                'html_url' => 'https://github.com/janusezam/SaaS-Multi-tenant-ISMS/releases/tag/v1.0.5',
                'published_at' => '2026-04-27T01:02:03Z',
            ], 201);
        }

        return Http::response([], 404);
    });

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'publish-github@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->post(route('central.business-control.support-updates.updates.store'), [
        'title' => 'My New Release',
        'summary' => 'Hello release notes',
        'version' => '',
        'source' => 'github',
        'is_published' => 1,
    ]);

    $response->assertRedirect(route('central.business-control.support-updates.index', absolute: false));

    $update = SystemUpdate::query()->firstWhere('version', 'v1.0.5');

    expect($update)->not->toBeNull();
    expect($update->source)->toBe('github');
    expect($update->title)->toBe('My New Release');
    expect($update->meta['github']['release_id'] ?? null)->toBe(123);
});

test('github publish is rejected when token is missing', function () {
    config()->set('services.github.owner', 'janusezam');
    config()->set('services.github.repo', 'SaaS-Multi-tenant-ISMS');
    config()->set('services.github.token', '');

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'publish-github-missing-token@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('central.business-control.support-updates.index'))
        ->post(route('central.business-control.support-updates.updates.store'), [
            'title' => 'My New Release',
            'summary' => 'Hello release notes',
            'source' => 'github',
            'is_published' => 1,
        ]);

    $response->assertRedirect(route('central.business-control.support-updates.index', absolute: false));
    $response->assertSessionHasErrors('source');
});
