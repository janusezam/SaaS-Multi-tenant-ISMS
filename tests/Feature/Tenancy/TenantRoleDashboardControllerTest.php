<?php

use App\Http\Controllers\Tenant\DashboardController;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('university admin receives admin dashboard view', function () {
    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $this->actingAs($user);

    $response = app(DashboardController::class)->index();

    expect($response->getName())->toBe('tenant.dashboards.university-admin');
});

test('sports facilitator receives facilitator dashboard view', function () {
    $user = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    $this->actingAs($user);

    $response = app(DashboardController::class)->index();

    expect($response->getName())->toBe('tenant.dashboards.sports-facilitator');
});

test('team coach receives coach dashboard view', function () {
    $user = User::factory()->create([
        'role' => 'team_coach',
    ]);

    $this->actingAs($user);

    $response = app(DashboardController::class)->index();

    expect($response->getName())->toBe('tenant.dashboards.team-coach');
});

test('student player receives student dashboard view', function () {
    $user = User::factory()->create([
        'role' => 'student_player',
    ]);

    $this->actingAs($user);

    $response = app(DashboardController::class)->index();

    expect($response->getName())->toBe('tenant.dashboards.student-player');
});

test('unknown role is rejected', function () {
    $user = User::factory()->create([
        'role' => 'guest',
    ]);

    $this->actingAs($user);

    try {
        app(DashboardController::class)->index();
        $this->fail('Expected unknown tenant role to be rejected.');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe(403);
    }
});
