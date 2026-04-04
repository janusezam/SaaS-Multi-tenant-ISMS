<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'must_change_password',
        'invite_token_hash',
        'invite_expires_at',
        'invite_sent_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'invite_expires_at' => 'datetime',
            'invite_sent_at' => 'datetime',
        ];
    }

    public function hasTenantRole(string $role): bool
    {
        return self::normalizeTenantRole($this->role) === self::normalizeTenantRole($role);
    }

    public static function normalizeTenantRole(?string $role): ?string
    {
        if ($role === null || trim($role) === '') {
            return null;
        }

        $normalized = strtolower(trim($role));

        return match ($normalized) {
            'university_admin', 'admin', 'university admin' => 'university_admin',
            'sports_facilitator', 'facilitator', 'sports facilitator' => 'sports_facilitator',
            'team_coach', 'coach', 'team coach' => 'team_coach',
            'student_player', 'player', 'student player' => 'student_player',
            default => $normalized,
        };
    }
}
