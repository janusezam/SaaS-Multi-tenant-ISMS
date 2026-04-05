<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdatePlayerAttendanceRequest;
use App\Models\GamePlayerAssignment;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class PlayerEngagementController extends Controller
{
    public function updateAttendance(UpdatePlayerAttendanceRequest $request, GamePlayerAssignment $assignment): RedirectResponse
    {
        if (! Schema::hasTable('game_player_assignments')) {
            return redirect()->back()->withErrors([
                'attendance' => 'Player assignment table is not migrated for this tenant yet.',
            ]);
        }

        $player = Player::query()->where('email', auth()->user()?->email)->first();

        abort_unless($player !== null, 403);
        abort_unless((int) $assignment->player_id === (int) $player->id, 403);

        $assignment->update([
            'attendance_status' => $request->validated('attendance_status'),
            'responded_at' => now(),
            'attendance_updated_by_user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('status', 'Attendance response submitted.');
    }
}
