<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionNotificationLog extends Model
{
    protected $fillable = [
        'university_id',
        'recipient_email',
        'notification_type',
        'notification_key',
        'subject',
        'details',
        'queued_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'queued_at' => 'datetime',
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_id', 'id');
    }
}
