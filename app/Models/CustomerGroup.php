<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customer\Database\Factories\CustomerGroupFactory;

class CustomerGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'customer_id',
        'group_name',
        'group_lavel',
        'group_type',
        'is_active',
    ];

    /**
     * protetion the data type
     */
    protected $case = [
        'is_active' => 'boolean',  
    ];

    /**
     * protection the factory
     */
    protected static function newFactory(): CustomerGroupFactory
    {
        return CustomerGroupFactory::new();
    }

    /**
     * reslation to the customer
     */
    public function customer() :HasMany
    {
        return $this->hasMany(Customer::class , 'customer_id');
    }
}
