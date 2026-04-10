<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUserRegistrationRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }
}
