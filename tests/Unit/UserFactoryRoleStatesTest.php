<?php

use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

test('user factory facilitator state sets sports facilitator role', function () {
    $user = User::factory()->facilitator()->make();

    expect($user->role)->toBe('sports_facilitator');
});

test('user factory coach state sets team coach role', function () {
    $user = User::factory()->coach()->make();

    expect($user->role)->toBe('team_coach');
});

test('user factory player state sets student player role', function () {
    $user = User::factory()->player()->make();

    expect($user->role)->toBe('student_player');
});
