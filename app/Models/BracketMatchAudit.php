<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BracketMatchAudit extends Model
{
    protected $fillable = [
        'bracket_match_id',
        'changed_by_user_id',
        'previous_winner_team_id',
        'new_winner_team_id',
    ];

    protected function casts(): array
    {
        return [
            'bracket_match_id' => 'integer',
            'changed_by_user_id' => 'integer',
            'previous_winner_team_id' => 'integer',
            'new_winner_team_id' => 'integer',
        ];
    }

    public function bracketMatch(): BelongsTo
    {
        return $this->belongsTo(BracketMatch::class);
    }

    public function previousWinnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'previous_winner_team_id');
    }

    public function newWinnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'new_winner_team_id');
    }
}
