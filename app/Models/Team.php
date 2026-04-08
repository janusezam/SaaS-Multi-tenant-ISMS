<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'sport_id',
        'name',
        'logo_path',
        'coach_name',
        'coach_email',
        'division',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function gameParticipations(): HasMany
    {
        return $this->hasMany(GameTeamParticipation::class);
    }

    public function playerAssignments(): HasMany
    {
        return $this->hasMany(GamePlayerAssignment::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(TeamAnnouncement::class);
    }
}
