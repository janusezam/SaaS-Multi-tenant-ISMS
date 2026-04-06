<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTeamRequest;
use App\Http\Requests\Tenant\UpdateTeamRequest;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Team::class, 'team');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('tenant.teams.index', [
            'teams' => Team::query()->with('sport')->latest()->paginate(12),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenant.teams.create', [
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'coaches' => User::query()->where('role', 'team_coach')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $coachUserId = $validated['coach_user_id'] ?? null;
        unset($validated['coach_user_id']);

        if ($coachUserId !== null) {
            $coach = User::query()
                ->where('id', $coachUserId)
                ->where('role', 'team_coach')
                ->first();

            if ($coach !== null) {
                $validated['coach_name'] = $coach->name;
                $validated['coach_email'] = $coach->email;
            }
        }

        Team::query()->create($validated);

        return redirect()->route('tenant.teams.index')->with('status', 'Team created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Team $team): View
    {
        $selectedCoachUserId = User::query()
            ->where('role', 'team_coach')
            ->where('email', $team->coach_email)
            ->value('id');

        return view('tenant.teams.edit', [
            'team' => $team,
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'coaches' => User::query()->where('role', 'team_coach')->orderBy('name')->get(),
            'selectedCoachUserId' => $selectedCoachUserId,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $validated = $request->validated();

        $coachUserId = $validated['coach_user_id'] ?? null;
        unset($validated['coach_user_id']);

        if ($coachUserId !== null) {
            $coach = User::query()
                ->where('id', $coachUserId)
                ->where('role', 'team_coach')
                ->first();

            if ($coach !== null) {
                $validated['coach_name'] = $coach->name;
                $validated['coach_email'] = $coach->email;
            }
        }

        $team->update($validated);

        return redirect()->route('tenant.teams.index')->with('status', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return redirect()->route('tenant.teams.index')->with('status', 'Team deleted successfully.');
    }
}
