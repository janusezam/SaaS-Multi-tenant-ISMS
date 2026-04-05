<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCoachAnnouncementRequest;
use App\Http\Requests\Tenant\UpdateCoachLineupRequest;
use App\Models\Game;
use App\Models\GamePlayerAssignment;
use App\Models\GameTeamParticipation;
use App\Models\Player;
use App\Models\Team;
use App\Models\TeamAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class CoachTeamController extends Controller
{
    public function updateLineup(UpdateCoachLineupRequest $request, Game $game): RedirectResponse
    {
        if (! Schema::hasTable('game_team_participations') || ! Schema::hasTable('game_player_assignments')) {
            return redirect()->back()->withErrors([
                'lineup' => 'Coach engagement tables are not migrated for this tenant yet.',
            ]);
        }

        $coachTeam = Team::query()->where('coach_email', auth()->user()?->email)->first();

        abort_unless($coachTeam !== null, 403);

        $isCoachGame = (int) $game->home_team_id === (int) $coachTeam->id
            || (int) $game->away_team_id === (int) $coachTeam->id;

        abort_unless($isCoachGame, 403);

        $validated = $request->validated();
        $playerIds = collect($validated['player_ids'])->map(fn (mixed $value): int => (int) $value)->unique()->values();
        $starterIds = collect($validated['starter_player_ids'] ?? [])->map(fn (mixed $value): int => (int) $value)->unique();

        $teamPlayers = Player::query()
            ->where('team_id', $coachTeam->id)
            ->whereIn('id', $playerIds)
            ->pluck('id');

        abort_unless($teamPlayers->count() === $playerIds->count(), 403);

        $coachConfirmed = $request->boolean('confirm_team');

        GameTeamParticipation::query()->updateOrCreate(
            [
                'game_id' => $game->id,
                'team_id' => $coachTeam->id,
            ],
            [
                'coach_confirmed_at' => $coachConfirmed ? now() : null,
                'coach_confirmed_by_user_id' => $coachConfirmed ? auth()->id() : null,
                'coach_note' => $validated['coach_note'] ?? null,
            ],
        );

        GamePlayerAssignment::query()
            ->where('game_id', $game->id)
            ->where('team_id', $coachTeam->id)
            ->delete();

        foreach ($teamPlayers as $playerId) {
            GamePlayerAssignment::query()->create([
                'game_id' => $game->id,
                'team_id' => $coachTeam->id,
                'player_id' => $playerId,
                'assigned_by_user_id' => auth()->id(),
                'is_starter' => $starterIds->contains($playerId),
                'attendance_status' => 'pending',
                'attendance_updated_by_user_id' => null,
            ]);
        }

        return redirect()->back()->with('status', 'Lineup updated and players assigned to match.');
    }

    public function storeAnnouncement(StoreCoachAnnouncementRequest $request): RedirectResponse
    {
        if (! Schema::hasTable('team_announcements')) {
            return redirect()->back()->withErrors([
                'announcement' => 'Team announcement table is not migrated for this tenant yet.',
            ]);
        }

        $coachTeam = Team::query()->where('coach_email', auth()->user()?->email)->first();

        abort_unless($coachTeam !== null, 403);

        TeamAnnouncement::query()->create([
            'team_id' => $coachTeam->id,
            'created_by_user_id' => auth()->id(),
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'published_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Announcement posted to your team.');
    }
}
