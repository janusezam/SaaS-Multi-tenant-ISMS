<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\User;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('sports facilitator can open dashboard but cannot access admin only modules', function () {
    $user = User::factory()->facilitator()->create();

    $dashboardResponse = $this->actingAs($user)->get(route('tenant.dashboard'));
    $dashboardResponse->assertOk();
    $dashboardResponse->assertSee('Manage and report games assigned to your sports.');

    $adminOnlyResponse = $this->actingAs($user)->get(route('tenant.users.index'));
    $adminOnlyResponse->assertForbidden();

    $this->actingAs($user)->get(route('tenant.sports.index'))->assertForbidden();
    $this->actingAs($user)->get(route('tenant.teams.index'))->assertForbidden();
    $this->actingAs($user)->get(route('tenant.players.index'))->assertForbidden();
    $this->actingAs($user)->get(route('tenant.pro.analytics'))->assertForbidden();
    $this->actingAs($user)->get(route('tenant.pro.bracket'))->assertForbidden();
});

test('team coach can access coach pages and not facilitator audits page', function () {
    $user = User::factory()->coach()->create();

    $dashboardResponse = $this->actingAs($user)->get(route('tenant.dashboard'));
    $dashboardResponse->assertOk();
    $dashboardResponse->assertSeeText("View your team's schedule, standing, and recent performance at a glance.", false);
    $dashboardResponse->assertSee('Upcoming Matches');
    $dashboardResponse->assertSee('My Team Next Matches');
    $dashboardResponse->assertSee('Recent Results (Read-Only)');

    $coachSchedulesResponse = $this->actingAs($user)->get(route('tenant.coach.schedules'));
    $coachSchedulesResponse->assertOk();
    $coachSchedulesResponse->assertSee('Use this timeline to prepare lineups and confirm team participation.');
    $coachSchedulesResponse->assertSee('Overview');
    $coachSchedulesResponse->assertSee('Upcoming');
    $coachSchedulesResponse->assertSee('Completed');

    $forbiddenResponse = $this->actingAs($user)->get(route('tenant.audits.game-results.index'));
    $forbiddenResponse->assertForbidden();

    $legacySchedulesResponse = $this->actingAs($user)->get('/app/coach/shedules');
    $legacySchedulesResponse->assertRedirect('/app/coach/schedules');

    $legacyTeamResponse = $this->actingAs($user)->get('/app/coach/my-teams');
    $legacyTeamResponse->assertRedirect('/app/coach/my-team');

});

test('student player can access my schedule and not coach pages', function () {
    $user = User::factory()->player()->create();

    $dashboardResponse = $this->actingAs($user)->get(route('tenant.dashboard'));
    $dashboardResponse->assertOk();
    $dashboardResponse->assertSee('View your upcoming games, latest team results, and current standing.');
    $dashboardResponse->assertSee('Next Match Date');
    $dashboardResponse->assertSee('My Upcoming Schedule');
    $dashboardResponse->assertSee('Recent Team Results');

    $scheduleResponse = $this->actingAs($user)->get(route('tenant.player.my-schedule'));
    $scheduleResponse->assertOk();
    $scheduleResponse->assertSee('Confirm your attendance, track your stats, and stay updated with team announcements.');
    $scheduleResponse->assertSee('Overview');
    $scheduleResponse->assertSee('Attendance');
    $scheduleResponse->assertSee('History');

    $forbiddenResponse = $this->actingAs($user)->get(route('tenant.coach.schedules'));
    $forbiddenResponse->assertForbidden();

    $legacyScheduleResponse = $this->actingAs($user)->get('/app/player/my-schedules');
    $legacyScheduleResponse->assertRedirect('/app/player/my-schedule');

    $legacyScheduleTypoResponse = $this->actingAs($user)->get('/app/player/my-shedule');
    $legacyScheduleTypoResponse->assertRedirect('/app/player/my-schedule');

});

test('university admin can access coach and player scoped routes due to global admin bypass', function () {
    $user = User::factory()->create(['role' => 'university_admin']);

    $coachRouteResponse = $this->actingAs($user)->get(route('tenant.coach.schedules'));
    $coachRouteResponse->assertOk();

    $playerRouteResponse = $this->actingAs($user)->get(route('tenant.player.my-schedule'));
    $playerRouteResponse->assertOk();
});
