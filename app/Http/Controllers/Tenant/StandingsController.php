<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Sport;
use App\Support\StandingsCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StandingsController extends Controller
{
    public function index(Request $request, StandingsCalculator $calculator): View
    {
        $sportId = $request->integer('sport_id') ?: null;
        $division = $request->string('division')->toString();

        $gamesQuery = Game::query()
            ->with(['sport', 'homeTeam', 'awayTeam', 'venue'])
            ->where('status', 'completed');

        if ($sportId !== null) {
            $gamesQuery->where('sport_id', $sportId);
        }

        $games = $gamesQuery->get();

        if ($division !== '') {
            $games = $games->filter(function (Game $game) use ($division): bool {
                return ($game->homeTeam?->division === $division) && ($game->awayTeam?->division === $division);
            })->values();
        }

        return view('tenant.standings.index', [
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedSportId' => $sportId,
            'selectedDivision' => $division,
            'standings' => $calculator->calculate($games),
        ]);
    }
}
