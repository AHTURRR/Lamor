<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrackingSession extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'phone_number',
        'token_hash',
        'label',
        'status',
        'expires_at',
        'activated_at',
        'created_by',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    /**
     * Get the admin who created this tracking session.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all location logs for this session.
     */
    public function locationLogs(): HasMany
    {
        return $this->hasMany(LocationLog::class, 'session_id');
    }

    /**
     * Get the latest location log for this session.
     */
    public function latestLocation(): HasOne
    {
        return $this->hasOne(LocationLog::class, 'session_id')->latestOfMany('created_at');
    }

    /**
     * Scope to only active sessions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope to only expired sessions.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('expires_at', '<=', now())
                ->orWhere('status', 'expired');
        });
    }

    /**
     * Scope to filter by phone number.
     */
    public function scopeForPhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone_number', $phone);
    }

    /**
     * Check if this session has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at->isPast() || $this->status === 'expired';
    }

    /**
     * Check if this session is currently trackable.
     */
    public function getIsTrackableAttribute(): bool
    {
        return in_array($this->status, ['pending', 'active']) && ! $this->is_expired;
    }

    /**
     * Get the public tracking URL for this session.
     * Note: Requires the plain token to be passed since we only store hashes.
     */
    public function getTrackingUrl(string $plainToken): string
    {
        return url("/track/{$plainToken}");
    }
}
