<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan',
        'start_date',
        'due_date',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'tenant_id', 'id');
    }
}
