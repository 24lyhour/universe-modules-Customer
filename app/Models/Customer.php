<?php

namespace Modules\Customer\Models;

use App\Traits\BelongsToOutlet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Modules\Customer\Database\Factories\CustomerFactory;
use Modules\Customer\Traits\TwoFactorAuthentication;
use \Modules\Wallets\Models\Wallet;

class Customer extends Model
{
    use HasApiTokens, HasFactory, TwoFactorAuthentication, BelongsToOutlet;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'movice_id',
        'customer_id',
        'outlet_id',
        'wallet_id',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'status',
        'provider_name',
        'provider_id',
        'avatar',
        'access_token',
        'two_factor_enabled',
        'email_verified_at',
        'phone_verified_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'access_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'two_factor_enabled' => 'boolean',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
        ];
    }

    /**
     * Relation to the movies rented by the customer.
     * Uses string class name to avoid errors if Movice module is not installed.
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\Movice\Models\Movice::class,
            'customer_movice',
            'customer_id',
            'movice_id'
        )->withTimestamps()->withPivot('rented_at', 'returned_at');
    }

    /**
     * Relation to the outlet.
     * Uses string class name to avoid errors if Outlet module is not installed.
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(\Modules\Outlet\Models\Outlet::class);
    }

    /**
     * Relation to the wallet.
     * Uses string class name to avoid errors if Wallets module is not installed.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relation to the parent/referrer customer.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relation to referred customers.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Customer::class, 'customer_id');
    }

    /**
     * Relation to the user who created this customer.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Relation to the user who last updated this customer.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope for active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive customers.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for suspended customers.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Check if customer is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if customer has verified email.
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Check if customer has verified phone.
     */
    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Scope for new customers (created within last 30 days).
     */
    public function scopeNew($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Scope for at-risk customers (no activity for 30-90 days).
     */
    public function scopeAtRisk($query)
    {
        return $query->active()
            ->where('updated_at', '<', now()->subDays(30))
            ->where('updated_at', '>=', now()->subDays(90));
    }

    /**
     * Scope for churned customers (inactive 90+ days or suspended/inactive status).
     */
    public function scopeChurned($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('status', ['inactive', 'suspended'])
                ->orWhere('updated_at', '<', now()->subDays(90));
        });
    }

    /**
     * Scope for returning customers (have activity after first creation).
     */
    public function scopeReturning($query)
    {
        return $query->active()
            ->whereColumn('updated_at', '>', 'created_at');
    }

    /**
     * Scope for VIP customers.
     */
    public function scopeVip($query)
    {
        return $query->active()
            ->whereNotNull('email_verified_at')
            ->where('updated_at', '>=', now()->subDays(7));
    }

    /**
     * Scope for customers created within a date range.
     */
    public function scopeCreatedBetween($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Get customer status category.
     */
    public function getStatusCategoryAttribute(): string
    {
        if ($this->status === 'suspended') {
            return 'churned';
        }

        if ($this->status === 'inactive') {
            return 'churned';
        }

        $daysSinceUpdate = $this->updated_at?->diffInDays(now()) ?? 0;

        if ($daysSinceUpdate >= 90) {
            return 'churned';
        }

        if ($daysSinceUpdate >= 30) {
            return 'at_risk';
        }

        $daysSinceCreation = $this->created_at->diffInDays(now());

        if ($daysSinceCreation <= 30) {
            return 'new';
        }

        if ($this->email_verified_at && $daysSinceUpdate <= 7) {
            return 'vip';
        }

        return 'active';
    }

    /**
     * Mark the customer's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Mark the customer's phone as verified.
     */
    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get full name attribute (alias).
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
