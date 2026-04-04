<?php

use App\Models\User;

test('tenant role normalization accepts legacy coach and player labels', function () {
    expect(User::normalizeTenantRole('Coach'))->toBe('team_coach');
    expect(User::normalizeTenantRole('student player'))->toBe('student_player');
});

test('tenant role normalization accepts legacy facilitator and admin labels', function () {
    expect(User::normalizeTenantRole('Sports Facilitator'))->toBe('sports_facilitator');
    expect(User::normalizeTenantRole('admin'))->toBe('university_admin');
});
