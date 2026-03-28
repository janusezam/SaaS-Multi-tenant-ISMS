<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreBracketResultRequest;
use App\Models\BracketMatch;
use App\Models\BracketMatchAudit;
use App\Models\Game;
use App\Models\GameResultAudit;
use App\Models\Sport;
use App\Models\Team;
use App\Support\StandingsCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProFeatureController extends Controller
{
    public function analytics(): View
    {
        $this->authorize('viewAny', BracketMatch::class);

        $isLocked = ! $this->isProPlan();

        return view('tenant.pro.analytics', [
            'isLocked' => $isLocked,
            'canRequestUpgrade' => auth()->user()?->role === 'university_admin',
            'totalSports' => Sport::query()->count(),
            'totalTeams' => Team::query()->count(),
            'totalGames' => Game::query()->count(),
            'completedGames' => Game::query()->where('status', 'completed')->count(),
        ]);
    }

    public function bracket(Request $request): View
    {
        $this->authorize('viewAny', BracketMatch::class);

        $isLocked = ! $this->isProPlan();

        $sportId = $request->integer('sport_id') ?: null;
        $storedBracketRounds = [];
        $previewRounds = [];

        if ($sportId !== null) {
            $storedBracketRounds = $this->storedBracketRounds($sportId);
        }

        if ($storedBracketRounds === []) {
            $teamsQuery = Team::query()->where('is_active', true)->orderBy('name');

            if ($sportId !== null) {
                $teamsQuery->where('sport_id', $sportId);
            }

            $teams = $teamsQuery->get();
            $previewRounds = $this->buildBracketRounds($this->seededTeamNames($teams, $sportId));
        }

        return view('tenant.pro.bracket', [
            'isLocked' => $isLocked,
            'canRequestUpgrade' => auth()->user()?->role === 'university_admin',
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedSportId' => $sportId,
            'rounds' => $storedBracketRounds !== [] ? $storedBracketRounds : $previewRounds,
            'hasStoredBracket' => $storedBracketRounds !== [],
        ]);
    }

    public function generateBracket(Request $request): RedirectResponse
    {
        $this->authorize('create', BracketMatch::class);

        $payload = $request->validate([
            'sport_id' => ['required', 'integer', 'exists:sports,id'],
        ]);

        $sportId = (int) $payload['sport_id'];

        $teams = Team::query()->where('sport_id', $sportId)->where('is_active', true)->orderBy('name')->get();
        $seededNames = $this->seededTeamNames($teams, $sportId);

        if (count($seededNames) < 2) {
            return redirect()->route('tenant.pro.bracket', ['sport_id' => $sportId])
                ->with('status', 'Add at least two active teams to generate a knockout bracket.');
        }

        $teamNameToId = $teams->pluck('id', 'name');
        $bracketStructure = $this->buildBracketRounds($seededNames);

        DB::transaction(function () use ($sportId, $bracketStructure, $teamNameToId): void {
            BracketMatch::query()->where('sport_id', $sportId)->delete();

            foreach ($bracketStructure as $roundIndex => $round) {
                $roundNumber = $roundIndex + 1;

                foreach ($round['matches'] as $matchIndex => $match) {
                    $homeLabel = (string) $match['home'];
                    $awayLabel = (string) $match['away'];
                    $homeTeamId = $teamNameToId->get($homeLabel);
                    $awayTeamId = $teamNameToId->get($awayLabel);

                    BracketMatch::query()->create([
                        'sport_id' => $sportId,
                        'round_number' => $roundNumber,
                        'match_number' => $matchIndex + 1,
                        'home_team_id' => is_numeric($homeTeamId) ? (int) $homeTeamId : null,
                        'away_team_id' => is_numeric($awayTeamId) ? (int) $awayTeamId : null,
                        'home_slot_label' => is_numeric($homeTeamId) ? null : $homeLabel,
                        'away_slot_label' => is_numeric($awayTeamId) ? null : $awayLabel,
                        'winner_team_id' => null,
                        'played_at' => null,
                    ]);
                }
            }

            $this->autoAdvanceByes($sportId);
        });

        return redirect()->route('tenant.pro.bracket', ['sport_id' => $sportId])->with('status', 'Knockout bracket generated.');
    }

    public function storeBracketResult(StoreBracketResultRequest $request, BracketMatch $match): RedirectResponse
    {
        $this->authorize('update', $match);

        $previousWinnerTeamId = $match->winner_team_id;
        $winnerTeamId = (int) $request->validated('winner_team_id');

        $match->update([
            'winner_team_id' => $winnerTeamId,
            'played_at' => now(),
        ]);

        if ($previousWinnerTeamId !== $winnerTeamId) {
            BracketMatchAudit::query()->create([
                'bracket_match_id' => $match->id,
                'changed_by_user_id' => auth()->id(),
                'previous_winner_team_id' => $previousWinnerTeamId,
                'new_winner_team_id' => $winnerTeamId,
            ]);
        }

        $this->advanceWinnerToNextRound($match->fresh(['sport']));

        return redirect()->route('tenant.pro.bracket', ['sport_id' => $match->sport_id])
            ->with('status', 'Bracket result recorded.');
    }

    public function bracketAudits(Request $request): View
    {
        $this->authorize('viewAny', BracketMatchAudit::class);

        $sportId = $request->integer('sport_id') ?: null;

        $query = BracketMatchAudit::query()
            ->with([
                'bracketMatch.sport',
                'bracketMatch.homeTeam',
                'bracketMatch.awayTeam',
                'previousWinnerTeam',
                'newWinnerTeam',
            ])
            ->latest();

        if ($sportId !== null) {
            $query->whereHas('bracketMatch', function ($matchQuery) use ($sportId): void {
                $matchQuery->where('sport_id', $sportId);
            });
        }

        return view('tenant.pro.bracket-audits', [
            'audits' => $query->paginate(20)->withQueryString(),
            'sports' => Sport::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedSportId' => $sportId,
        ]);
    }

    public function exportStandingsCsv(Request $request, StandingsCalculator $calculator): Response
    {
        $this->authorize('viewAny', BracketMatch::class);

        $rows = $this->standingsRows($request, $calculator);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Team', 'Played', 'Wins', 'Draws', 'Losses', 'GF', 'GA', 'GD', 'Points']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['team'],
                    $row['played'],
                    $row['wins'],
                    $row['draws'],
                    $row['losses'],
                    $row['gf'],
                    $row['ga'],
                    $row['gd'],
                    $row['points'],
                ]);
            }

            fclose($handle);
        }, 'standings.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportStandingsPdf(Request $request, StandingsCalculator $calculator): Response
    {
        $this->authorize('viewAny', BracketMatch::class);

        $rows = $this->standingsRows($request, $calculator);

        $pdf = Pdf::loadView('tenant.pro.exports.standings-pdf', [
            'rows' => $rows,
        ]);

        return $pdf->download('standings.pdf');
    }

    public function exportResultAuditsCsv(Request $request): Response
    {
        $this->authorize('viewAny', BracketMatch::class);

        $rows = $this->resultAuditRows($request);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Changed At', 'Sport', 'Match', 'Changed By', 'Previous Status', 'New Status', 'Previous Score', 'New Score']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['changed_at'],
                    $row['sport'],
                    $row['match'],
                    $row['changed_by'],
                    $row['previous_status'],
                    $row['new_status'],
                    $row['previous_score'],
                    $row['new_score'],
                ]);
            }

            fclose($handle);
        }, 'result-audits.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportResultAuditsPdf(Request $request): Response
    {
        $this->authorize('viewAny', BracketMatch::class);

        $rows = $this->resultAuditRows($request);

        $pdf = Pdf::loadView('tenant.pro.exports.result-audits-pdf', [
            'rows' => $rows,
        ]);

        return $pdf->download('result-audits.pdf');
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function standingsRows(Request $request, StandingsCalculator $calculator): array
    {
        $sportId = $request->integer('sport_id') ?: null;
        $division = $request->string('division')->toString();

        $gamesQuery = Game::query()->with(['homeTeam', 'awayTeam'])->where('status', 'completed');

        if ($sportId !== null) {
            $gamesQuery->where('sport_id', $sportId);
        }

        $games = $gamesQuery->get();

        if ($division !== '') {
            $games = $games->filter(function (Game $game) use ($division): bool {
                return ($game->homeTeam?->division === $division) && ($game->awayTeam?->division === $division);
            })->values();
        }

        return $calculator->calculate($games);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function resultAuditRows(Request $request): array
    {
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

        return $query->get()->map(function (GameResultAudit $audit): array {
            $homeTeamName = $audit->game?->homeTeam?->name ?? '-';
            $awayTeamName = $audit->game?->awayTeam?->name ?? '-';

            return [
                'changed_at' => (string) $audit->created_at?->format('Y-m-d H:i:s'),
                'sport' => (string) ($audit->game?->sport?->name ?? '-'),
                'match' => $homeTeamName.' vs '.$awayTeamName,
                'changed_by' => $audit->changed_by_user_id !== null ? (string) $audit->changed_by_user_id : 'System',
                'previous_status' => strtoupper($audit->previous_status),
                'new_status' => strtoupper($audit->new_status),
                'previous_score' => ($audit->previous_home_score ?? '-').'-'.($audit->previous_away_score ?? '-'),
                'new_score' => ($audit->new_home_score ?? '-').'-'.($audit->new_away_score ?? '-'),
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, Team>  $teams
     * @return array<int, string>
     */
    private function seededTeamNames(Collection $teams, ?int $sportId): array
    {
        $completedGamesQuery = Game::query()->with(['homeTeam', 'awayTeam'])->where('status', 'completed');

        if ($sportId !== null) {
            $completedGamesQuery->where('sport_id', $sportId);
        }

        $standings = app(StandingsCalculator::class)->calculate($completedGamesQuery->get());

        $standingsTeamNames = array_map(
            static fn (array $row): string => (string) $row['team'],
            $standings
        );

        $alphabeticalTeamNames = $teams->pluck('name')->all();
        $missingTeamNames = array_values(array_diff($alphabeticalTeamNames, $standingsTeamNames));

        return array_values(array_unique([...$standingsTeamNames, ...$missingTeamNames]));
    }

    /**
     * @param  array<int, string>  $seededTeamNames
     * @return array<int, array<string, mixed>>
     */
    private function buildBracketRounds(array $seededTeamNames): array
    {
        $teamCount = count($seededTeamNames);

        if ($teamCount < 2) {
            return [];
        }

        $bracketSize = 1;

        while ($bracketSize < $teamCount) {
            $bracketSize *= 2;
        }

        $participants = $seededTeamNames;

        while (count($participants) < $bracketSize) {
            $participants[] = 'BYE';
        }

        $matchesInRound = intdiv($bracketSize, 2);
        $rounds = [];
        $roundIndex = 0;

        while ($matchesInRound >= 1) {
            $matches = [];

            if ($roundIndex === 0) {
                for ($index = 0; $index < $matchesInRound; $index++) {
                    $home = $participants[$index] ?? 'BYE';
                    $away = $participants[$bracketSize - 1 - $index] ?? 'BYE';

                    $matches[] = [
                        'home' => $home,
                        'away' => $away,
                    ];
                }
            } else {
                for ($matchNumber = 1; $matchNumber <= $matchesInRound; $matchNumber++) {
                    $previousMatchA = ($matchNumber * 2) - 1;
                    $previousMatchB = $matchNumber * 2;

                    $matches[] = [
                        'home' => 'Winner of Match '.$previousMatchA,
                        'away' => 'Winner of Match '.$previousMatchB,
                    ];
                }
            }

            $rounds[] = [
                'name' => $this->roundLabel($matchesInRound),
                'matches' => $matches,
            ];

            $matchesInRound = intdiv($matchesInRound, 2);
            $roundIndex++;
        }

        return $rounds;
    }

    private function roundLabel(int $matchesInRound): string
    {
        if ($matchesInRound === 1) {
            return 'Final';
        }

        if ($matchesInRound === 2) {
            return 'Semifinals';
        }

        if ($matchesInRound === 4) {
            return 'Quarterfinals';
        }

        if ($matchesInRound === 8) {
            return 'Round of 16';
        }

        return 'Round of '.($matchesInRound * 2);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function storedBracketRounds(int $sportId): array
    {
        $matches = BracketMatch::query()
            ->with(['homeTeam', 'awayTeam', 'winnerTeam'])
            ->where('sport_id', $sportId)
            ->orderBy('round_number')
            ->orderBy('match_number')
            ->get();

        if ($matches->isEmpty()) {
            return [];
        }

        return $matches
            ->groupBy('round_number')
            ->map(function (Collection $roundMatches, int $roundNumber): array {
                return [
                    'name' => $this->roundLabel($roundMatches->count()),
                    'round_number' => $roundNumber,
                    'matches' => $roundMatches->map(function (BracketMatch $match): array {
                        return [
                            'id' => $match->id,
                            'home' => $match->homeTeam?->name ?? ($match->home_slot_label ?? 'TBD'),
                            'away' => $match->awayTeam?->name ?? ($match->away_slot_label ?? 'TBD'),
                            'home_team_id' => $match->home_team_id,
                            'away_team_id' => $match->away_team_id,
                            'winner_team_id' => $match->winner_team_id,
                            'winner' => $match->winnerTeam?->name,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function autoAdvanceByes(int $sportId): void
    {
        $openMatches = BracketMatch::query()
            ->where('sport_id', $sportId)
            ->whereNull('winner_team_id')
            ->orderBy('round_number')
            ->orderBy('match_number')
            ->get();

        foreach ($openMatches as $match) {
            $home = $match->home_team_id;
            $away = $match->away_team_id;

            if (($home !== null && $away === null) || ($home === null && $away !== null)) {
                $winner = $home ?? $away;

                if ($winner !== null) {
                    $match->update([
                        'winner_team_id' => $winner,
                    ]);

                    $this->advanceWinnerToNextRound($match);
                }
            }
        }
    }

    private function advanceWinnerToNextRound(BracketMatch $match): void
    {
        if ($match->winner_team_id === null) {
            return;
        }

        $nextRoundNumber = $match->round_number + 1;
        $nextMatchNumber = (int) ceil($match->match_number / 2);

        $nextMatch = BracketMatch::query()
            ->where('sport_id', $match->sport_id)
            ->where('round_number', $nextRoundNumber)
            ->where('match_number', $nextMatchNumber)
            ->first();

        if ($nextMatch === null) {
            return;
        }

        if ($match->match_number % 2 === 1) {
            $nextMatch->home_team_id = $match->winner_team_id;
            $nextMatch->home_slot_label = null;
        } else {
            $nextMatch->away_team_id = $match->winner_team_id;
            $nextMatch->away_slot_label = null;
        }

        $nextMatch->save();

        $home = $nextMatch->home_team_id;
        $away = $nextMatch->away_team_id;

        if (($home !== null && $away === null) || ($home === null && $away !== null)) {
            $nextMatch->update([
                'winner_team_id' => $home ?? $away,
            ]);

            $this->advanceWinnerToNextRound($nextMatch);
        }
    }

    private function isProPlan(): bool
    {
        return tenant()?->currentPlan() === 'pro';
    }
}
