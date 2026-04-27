<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreGameRequest;
use App\Http\Requests\Tenant\StoreGameResultRequest;
use App\Http\Requests\Tenant\UpdateGameRequest;
use App\Models\Game;
use App\Models\GameResultAudit;
use App\Models\Sport;
use App\Models\Team;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Game::class, 'game');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('tenant.games.index', [
            'games' => Game::query()->with(['sport', 'homeTeam', 'awayTeam', 'venue'])->latest()->paginate(12),
        ]);
    }

    /**
     * Display tenant-wide result change history with filters.
     */
    public function auditsIndex(Request $request): View
    {
        $this->authorize('viewAny', GameResultAudit::class);

        $sportId = $request->integer('sport_id') ?: null;
        $changedByUserId = $request->integer('changed_by_user_id') ?: null;
        $fromDate = $request->string('from_date')->toString();
        $toDate = $request->string('to_date')->toString();

        $query = GameResultAudit::query()
            ->with(['game.sport', 'game.homeTeam', 'game.awayTeam'])
            ->latest();

        if ($sportId !== null) {
            $query->whereHas('game', function ($gameQuery) use ($sportId): void {
                $gameQuery->where('sport_id', $sportId);
            });
        }

        if ($changedByUserId !== null) {
            $query->where('changed_by_user_id', $changedByUserId);
        }

        if ($fromDate !== '') {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate !== '') {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $audits = $query->paginate(20)->withQueryString();

        return view('tenant.games.audits-index', [
            'audits' => $audits,
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => [
                'sport_id' => $sportId,
                'changed_by_user_id' => $changedByUserId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenant.games.create', [
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'teams' => Team::query()->where('is_active', true)->orderBy('name')->get(),
            'venues' => Venue::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGameRequest $request): RedirectResponse
    {
        Game::query()->create($request->validated());

        return redirect()->route('tenant.games.index')->with('status', 'Game scheduled successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Game $game): View
    {
        return view('tenant.games.edit', [
            'game' => $game,
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'teams' => Team::query()->where('is_active', true)->orderBy('name')->get(),
            'venues' => Venue::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Show result change history for a game.
     */
    public function auditTrail(Game $game): View
    {
        $this->authorize('view', $game);

        return view('tenant.games.audit-trail', [
            'game' => $game->load(['sport', 'homeTeam', 'awayTeam']),
            'audits' => $game->resultAudits()->latest()->paginate(20),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGameRequest $request, Game $game): RedirectResponse
    {
        $game->update($request->validated());

        return redirect()->route('tenant.games.index')->with('status', 'Game updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game): RedirectResponse
    {
        $game->delete();

        return redirect()->route('tenant.games.index')->with('status', 'Game deleted successfully.');
    }

    /**
     * Submit a game result and lock in the status.
     */
    public function submitResult(StoreGameResultRequest $request, Game $game): RedirectResponse
    {
        $this->authorize('update', $game);

        $payload = $request->validated();

        $previousStatus = $game->status;
        $previousHomeScore = $game->home_score;
        $previousAwayScore = $game->away_score;

        $newStatus = $payload['status'];
        $newHomeScore = $payload['home_score'] ?? null;
        $newAwayScore = $payload['away_score'] ?? null;

        $game->update([
            'status' => $newStatus,
            'home_score' => $newHomeScore,
            'away_score' => $newAwayScore,
        ]);

        GameResultAudit::query()->create([
            'game_id' => $game->id,
            'changed_by_user_id' => auth()->id(),
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'previous_home_score' => $previousHomeScore,
            'new_home_score' => $newHomeScore,
            'previous_away_score' => $previousAwayScore,
            'new_away_score' => $newAwayScore,
        ]);

        return redirect()->route('tenant.games.index')->with('status', 'Game result submitted successfully.');
    }
}
