<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePlayerAssignment;
use App\Models\GameTeamParticipation;
use App\Models\Player;
use App\Models\Team;
use App\Models\TeamAnnouncement;
use App\Support\TenantPermissionMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CoachModuleController extends Controller
{
    public function schedules(): View
    {
        $hasTeamsTable = Schema::hasTable('teams');
        $hasGamesTable = Schema::hasTable('games');

        $coachUser = auth()->user();
        $myTeam = null;
        $upcomingMatches = collect();
        $completedMatches = collect();
        $nextMatch = null;

        if ($hasTeamsTable && $coachUser !== null) {
            $myTeam = Team::query()
                ->with('sport')
                ->where('coach_email', $coachUser->email)
                ->first();
        }

        if ($hasGamesTable && $myTeam !== null) {
            $upcomingMatches = Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue', 'sport'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->get();

            $completedMatches = Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue', 'sport'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(10)
                ->get();

            $nextMatch = $upcomingMatches->first();
        }

        return view('tenant.coach.schedules', [
            'myTeam' => $myTeam,
            'upcomingMatches' => $upcomingMatches,
            'completedMatches' => $completedMatches,
            'nextMatch' => $nextMatch,
        ]);
    }

    public function myTeam(): View
    {
        $hasTeamsTable = Schema::hasTable('teams');
        $hasPlayersTable = Schema::hasTable('players');
        $hasGamesTable = Schema::hasTable('games');
        $hasParticipationTables = Schema::hasTable('game_team_participations')
            && Schema::hasTable('game_player_assignments');
        $hasAnnouncementsTable = Schema::hasTable('team_announcements');

        $permissionMatrix = app(TenantPermissionMatrix::class);
        $canManageLineup = $permissionMatrix->allows(auth()->user(), 'coach.lineup.manage');
        $canManageAnnouncements = $permissionMatrix->allows(auth()->user(), 'coach.announcements.manage');

        $coachUser = auth()->user();
        $myTeam = null;
        $roster = collect();
        $upcomingMatches = collect();
        $assignmentsByGame = collect();
        $participationsByGame = collect();
        $announcements = collect();
        $recentResults = collect();
        $wins = 0;
        $losses = 0;
        $draws = 0;
        $pointsFor = 0;
        $pointsAgainst = 0;

        if ($hasTeamsTable && $coachUser !== null) {
            $myTeam = Team::query()
                ->with('sport')
                ->where('coach_email', $coachUser->email)
                ->first();
        }

        if ($hasPlayersTable && $myTeam !== null) {
            $roster = Player::query()
                ->where('team_id', $myTeam->id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            // Link to users to get profile photos
            $emails = $roster->pluck('email')->filter()->values();
            $rosterUsers = \App\Models\User::query()
                ->whereIn('email', $emails)
                ->get()
                ->keyBy('email');
            
            $roster->each(function($player) use ($rosterUsers) {
                $player->user = $rosterUsers->get($player->email);
            });
        }

        if ($hasGamesTable && $myTeam !== null) {
            $upcomingMatches = Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get();

            $recentResults = Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(6)
                ->get();

            foreach ($recentResults as $game) {
                $isHome = (int) $game->home_team_id === (int) $myTeam->id;
                $myScore = $isHome ? (int) ($game->home_score ?? 0) : (int) ($game->away_score ?? 0);
                $opponentScore = $isHome ? (int) ($game->away_score ?? 0) : (int) ($game->home_score ?? 0);

                $pointsFor += $myScore;
                $pointsAgainst += $opponentScore;

                if ($myScore > $opponentScore) {
                    $wins++;
                } elseif ($myScore < $opponentScore) {
                    $losses++;
                } else {
                    $draws++;
                }
            }
        }

        if ($hasParticipationTables && $myTeam !== null && $upcomingMatches->isNotEmpty()) {
            $gameIds = $upcomingMatches->pluck('id');

            $assignmentsByGame = GamePlayerAssignment::query()
                ->where('team_id', $myTeam->id)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->groupBy('game_id');

            $participationsByGame = GameTeamParticipation::query()
                ->where('team_id', $myTeam->id)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->keyBy('game_id');
        }

        if ($hasAnnouncementsTable && $myTeam !== null) {
            $announcements = TeamAnnouncement::query()
                ->where('team_id', $myTeam->id)
                ->latest('published_at')
                ->latest('id')
                ->limit(8)
                ->get();
        }

        return view('tenant.coach.my-team', [
            'myTeam' => $myTeam,
            'roster' => $roster,
            'upcomingMatches' => $upcomingMatches,
            'assignmentsByGame' => $assignmentsByGame,
            'participationsByGame' => $participationsByGame,
            'announcements' => $announcements,
            'recentResults' => $recentResults,
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'pointsFor' => $pointsFor,
            'pointsAgainst' => $pointsAgainst,
            'canManageLineup' => $canManageLineup,
            'canManageAnnouncements' => $canManageAnnouncements,
            'hasParticipationTables' => $hasParticipationTables,
            'hasAnnouncementsTable' => $hasAnnouncementsTable,
        ]);
    }
}
