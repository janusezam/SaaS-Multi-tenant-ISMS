<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'sport_id',
        'home_team_id',
        'away_team_id',
        'venue_id',
        'scheduled_at',
        'status',
        'home_score',
        'away_score',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'home_score' => 'integer',
            'away_score' => 'integer',
        ];
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function resultAudits(): HasMany
    {
        return $this->hasMany(GameResultAudit::class);
    }

    public function teamParticipations(): HasMany
    {
        return $this->hasMany(GameTeamParticipation::class);
    }

    public function playerAssignments(): HasMany
    {
        return $this->hasMany(GamePlayerAssignment::class);
    }
}
