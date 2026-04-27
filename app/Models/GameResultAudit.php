<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameResultAudit extends Model
{
    protected $fillable = [
        'game_id',
        'changed_by_user_id',
        'previous_status',
        'new_status',
        'previous_home_score',
        'new_home_score',
        'previous_away_score',
        'new_away_score',
    ];

    protected function casts(): array
    {
        return [
            'game_id' => 'integer',
            'changed_by_user_id' => 'integer',
            'previous_home_score' => 'integer',
            'new_home_score' => 'integer',
            'previous_away_score' => 'integer',
            'new_away_score' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
