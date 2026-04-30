<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePlayerAssignment;
use App\Models\Player;
use App\Models\TeamAnnouncement;
use App\Support\StandingsCalculator;
use App\Support\TenantPermissionMatrix;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PlayerModuleController extends Controller
{
    public function mySchedule(): View
    {
        $hasPlayersTable = Schema::hasTable('players');
        $hasGamesTable = Schema::hasTable('games');
        $hasAssignmentsTable = Schema::hasTable('game_player_assignments');
        $hasAnnouncementsTable = Schema::hasTable('team_announcements');

        $permissionMatrix = app(TenantPermissionMatrix::class);
        $canRespondAttendance = $permissionMatrix->allows(auth()->user(), 'player.attendance.respond');
        $canViewRoster = $permissionMatrix->allows(auth()->user(), 'player.roster.view');
        $canViewAnnouncements = $permissionMatrix->allows(auth()->user(), 'player.announcements.view');
        $canViewHistory = $permissionMatrix->allows(auth()->user(), 'player.history.view');

        $playerUser = auth()->user();
        $playerProfile = null;
        $myTeam = null;
        $teamRoster = collect();
        $upcomingMatches = collect();
        $recentResults = collect();
        $myCompletedMatches = collect();
        $assignmentsByGame = collect();
        $announcements = collect();
        $nextMatchDate = null;
        $standingRank = null;
        $lastMatchResult = 'N/A';
        $acceptedCount = 0;
        $declinedCount = 0;
        $pendingCount = 0;
        $starterCount = 0;

        if ($hasPlayersTable && $playerUser !== null) {
            $playerProfile = Player::query()
                ->with(['team.sport'])
                ->where('email', $playerUser->email)
                ->first();

            $myTeam = $playerProfile?->team;

            if ($myTeam !== null) {
                $teamRoster = Player::query()
                    ->where('team_id', $myTeam->id)
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();

                // Link to users to get profile photos
                $emails = $teamRoster->pluck('email')->filter()->values();
                $rosterUsers = \App\Models\User::query()
                    ->whereIn('email', $emails)
                    ->get()
                    ->keyBy('email');
                
                $teamRoster->each(function($player) use ($rosterUsers) {
                    $player->user = $rosterUsers->get($player->email);
                });
            }
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

            $recentResults = Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue', 'sport'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(10)
                ->get();

            $nextMatchDate = $upcomingMatches->first()?->scheduled_at;

            $latestMatch = $recentResults->first();
            if ($latestMatch !== null) {
                $isHome = (int) $latestMatch->home_team_id === (int) $myTeam->id;
                $myScore = $isHome ? (int) ($latestMatch->home_score ?? 0) : (int) ($latestMatch->away_score ?? 0);
                $opponentScore = $isHome ? (int) ($latestMatch->away_score ?? 0) : (int) ($latestMatch->home_score ?? 0);
                $lastMatchResult = $myScore > $opponentScore ? 'Win' : ($myScore < $opponentScore ? 'Loss' : 'Draw');
            }

            if ($myTeam->sport_id !== null) {
                $completedSportGames = Game::query()
                    ->with(['homeTeam', 'awayTeam'])
                    ->where('sport_id', $myTeam->sport_id)
                    ->where('status', 'completed')
                    ->get();

                $standingsRows = app(StandingsCalculator::class)->calculate($completedSportGames);

                foreach ($standingsRows as $index => $row) {
                    if (($row['team'] ?? null) === $myTeam->name) {
                        $standingRank = $index + 1;
                        break;
                    }
                }
            }
        }

        if ($hasAssignmentsTable && $playerProfile !== null && $myTeam !== null) {
            $myAssignments = GamePlayerAssignment::query()
                ->with(['game.homeTeam', 'game.awayTeam', 'assignedBy'])
                ->where('player_id', $playerProfile->id)
                ->where('team_id', $myTeam->id)
                ->get();

            $assignmentsByGame = $myAssignments->keyBy('game_id');
            $acceptedCount = $myAssignments->where('attendance_status', 'accepted')->count();
            $declinedCount = $myAssignments->where('attendance_status', 'declined')->count();
            $pendingCount = $myAssignments->where('attendance_status', 'pending')->count();
            $starterCount = $myAssignments->where('is_starter', true)->count();

            $myCompletedMatches = $myAssignments
                ->filter(fn ($assignment) => $assignment->game?->status === 'completed')
                ->sortByDesc(fn ($assignment) => $assignment->game?->scheduled_at)
                ->take(10);
        }

        if ($hasAnnouncementsTable && $myTeam !== null) {
            $announcements = TeamAnnouncement::query()
                ->where('team_id', $myTeam->id)
                ->latest('published_at')
                ->latest('id')
                ->limit(8)
                ->get();
        }

        return view('tenant.player.my-schedule', [
            'playerProfile' => $playerProfile,
            'myTeam' => $myTeam,
            'teamRoster' => $teamRoster,
            'upcomingMatches' => $upcomingMatches,
            'recentResults' => $recentResults,
            'myCompletedMatches' => $myCompletedMatches,
            'assignmentsByGame' => $assignmentsByGame,
            'announcements' => $announcements,
            'nextMatchDate' => $nextMatchDate,
            'standingRank' => $standingRank,
            'lastMatchResult' => $lastMatchResult,
            'acceptedCount' => $acceptedCount,
            'declinedCount' => $declinedCount,
            'pendingCount' => $pendingCount,
            'starterCount' => $starterCount,
            'canRespondAttendance' => $canRespondAttendance,
            'canViewRoster' => $canViewRoster,
            'canViewAnnouncements' => $canViewAnnouncements,
            'canViewHistory' => $canViewHistory,
            'hasAnnouncementsTable' => $hasAnnouncementsTable,
        ]);
    }
}
