<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomerShipping extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'customer_shipping';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'customer_id',
        'label',
        'recipient_name',
        'phone_number',
        'province',
        'district',
        'commune',
        'street_address',
        'house_number',
        'floor',
        'landmark',
        'note',
        'latitude',
        'longitude',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // When setting a new default address, unset others
        static::saving(function ($model) {
            if ($model->is_default && $model->isDirty('is_default')) {
                static::where('customer_id', $model->customer_id)
                    ->where('id', '!=', $model->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the customer that owns the shipping address.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get full address as a string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [];

        if ($this->house_number) {
            $parts[] = $this->house_number;
        }

        $parts[] = $this->street_address;
        $parts[] = $this->commune;
        $parts[] = $this->district;
        $parts[] = $this->province;

        return implode(', ', $parts);
    }

    /**
     * Scope for default addresses.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for customer addresses.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
