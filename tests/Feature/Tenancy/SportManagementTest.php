<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    if (! Schema::hasTable('sports')) {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('sports facilitator can create and update a sport', function () {
    $user = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    $response = $this->actingAs($user)->post(route('tenant.sports.store'), [
        'name' => 'Basketball',
        'code' => 'bball',
        'description' => 'Court sport',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.sports.index'));
    $this->assertDatabaseHas('sports', ['name' => 'Basketball', 'code' => 'bball']);

    $sportId = Sport::query()->where('code', 'bball')->firstOrFail()->id;

    $response = $this->actingAs($user)->put(route('tenant.sports.update', $sportId), [
        'name' => 'Basketball League',
        'code' => 'bball',
        'description' => 'Updated',
        'is_active' => false,
    ]);

    $response->assertRedirect(route('tenant.sports.index'));
    $this->assertDatabaseHas('sports', ['id' => $sportId, 'name' => 'Basketball League', 'is_active' => 0]);
});

test('team coach is forbidden from sports management routes', function () {
    $user = User::factory()->create([
        'role' => 'team_coach',
    ]);

    $response = $this->actingAs($user)->get(route('tenant.sports.index'));

    $response->assertForbidden();
});

test('university admin can delete sport', function () {
    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $this->actingAs($user)->post(route('tenant.sports.store'), [
        'name' => 'Volleyball',
        'code' => 'vball',
        'description' => null,
        'is_active' => true,
    ]);

    $sportId = Sport::query()->where('code', 'vball')->firstOrFail()->id;

    $response = $this->actingAs($user)->delete(route('tenant.sports.destroy', $sportId));

    $response->assertRedirect(route('tenant.sports.index'));
    $this->assertDatabaseMissing('sports', ['id' => $sportId]);
});
