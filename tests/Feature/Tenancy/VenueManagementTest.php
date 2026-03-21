<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    if (! Schema::hasTable('venues')) {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->integer('capacity')->default(0);
            $table->string('surface_type')->nullable();
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

test('sports facilitator can create and update a venue', function () {
    $user = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    $response = $this->actingAs($user)->post(route('tenant.venues.store'), [
        'name' => 'Main Court',
        'location' => 'North Campus',
        'capacity' => 1200,
        'surface_type' => 'Hardwood',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.venues.index'));
    $this->assertDatabaseHas('venues', ['name' => 'Main Court', 'location' => 'North Campus']);

    $venueId = Venue::query()->where('name', 'Main Court')->firstOrFail()->id;

    $response = $this->actingAs($user)->put(route('tenant.venues.update', $venueId), [
        'name' => 'Main Court A',
        'location' => 'North Campus',
        'capacity' => 1400,
        'surface_type' => 'Hardwood',
        'is_active' => false,
    ]);

    $response->assertRedirect(route('tenant.venues.index'));
    $this->assertDatabaseHas('venues', ['id' => $venueId, 'name' => 'Main Court A', 'capacity' => 1400, 'is_active' => 0]);
});

test('student player is forbidden from venue management routes', function () {
    $user = User::factory()->create([
        'role' => 'student_player',
    ]);

    $response = $this->actingAs($user)->get(route('tenant.venues.index'));

    $response->assertForbidden();
});

test('university admin can delete venue', function () {
    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $this->actingAs($user)->post(route('tenant.venues.store'), [
        'name' => 'Field One',
        'location' => 'West Complex',
        'capacity' => 800,
        'surface_type' => 'Grass',
        'is_active' => true,
    ]);

    $venueId = Venue::query()->where('name', 'Field One')->firstOrFail()->id;

    $response = $this->actingAs($user)->delete(route('tenant.venues.destroy', $venueId));

    $response->assertRedirect(route('tenant.venues.index'));
    $this->assertDatabaseMissing('venues', ['id' => $venueId]);
});
