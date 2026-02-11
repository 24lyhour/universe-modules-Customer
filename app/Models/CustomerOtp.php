<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOtp extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'phone',
        'code',
        'type',
        'verified',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /**
     * Relation to the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid.
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->verified && $this->attempts < 5;
    }

    /**
     * Check if max attempts reached.
     */
    public function maxAttemptsReached(): bool
    {
        return $this->attempts >= 5;
    }

    /**
     * Increment attempts.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Mark as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Scope for active (non-expired, non-verified) OTPs.
     */
    public function scopeActive($query)
    {
        return $query->where('verified', false)
            ->where('expires_at', '>', now())
            ->where('attempts', '<', 5);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
