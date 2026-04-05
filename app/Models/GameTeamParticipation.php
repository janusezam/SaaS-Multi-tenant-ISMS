<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameTeamParticipation extends Model
{
    protected $fillable = [
        'game_id',
        'team_id',
        'coach_confirmed_at',
        'coach_confirmed_by_user_id',
        'coach_note',
    ];

    protected function casts(): array
    {
        return [
            'coach_confirmed_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function coachConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_confirmed_by_user_id');
    }
}
