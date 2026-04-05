<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayerAssignment extends Model
{
    protected $fillable = [
        'game_id',
        'team_id',
        'player_id',
        'assigned_by_user_id',
        'is_starter',
        'attendance_status',
        'responded_at',
        'attendance_updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_starter' => 'boolean',
            'responded_at' => 'datetime',
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

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function attendanceUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendance_updated_by_user_id');
    }
}
