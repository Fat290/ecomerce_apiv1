<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'token',
        'token_hash',
        'device_id',
        'device_name',
        'ip_address',
        'user_agent',
        'expires_at',
        'last_used_at',
        'is_revoked',
        'replaced_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'is_revoked' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the refresh token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the token that replaced this one.
     */
    public function replacedBy()
    {
        return $this->belongsTo(RefreshToken::class, 'replaced_by');
    }

    /**
     * Get the token that this one replaced.
     */
    public function replaces()
    {
        return $this->hasOne(RefreshToken::class, 'replaced_by');
    }

    /**
     * Check if the token has been reused (already revoked and replaced).
     */
    public function hasBeenReused(): bool
    {
        return $this->is_revoked && $this->replaced_by !== null;
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the token is valid (not revoked and not expired).
     */
    public function isValid(): bool
    {
        return !$this->is_revoked && !$this->isExpired();
    }

    /**
     * Generate a unique refresh token.
     * Note: This method is deprecated - refresh tokens are now JWT tokens.
     * Kept for backward compatibility if needed.
     *
     * @deprecated Use JWT for refresh tokens instead
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }
}
