<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class BracketMatch extends Model
{
    protected $fillable = [
        'sport_id',
        'round_number',
        'match_number',
        'home_team_id',
        'away_team_id',
        'home_slot_label',
        'away_slot_label',
        'winner_team_id',
        'played_at',
    ];

    protected function casts(): array
    {
        return [
            'sport_id' => 'integer',
            'round_number' => 'integer',
            'match_number' => 'integer',
            'home_team_id' => 'integer',
            'away_team_id' => 'integer',
            'winner_team_id' => 'integer',
            'played_at' => 'datetime',
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

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(BracketMatchAudit::class);
    }
}
