<?php

namespace Modules\Customer\Actions\Api;

use Modules\Customer\Models\Customer;

class GetCustomerProfileAction
{
    /**
     * Execute the get profile action.
     */
    public function execute(Customer $customer): array
    {
        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar' => $customer->avatar,
                'gender' => $customer->gender,
                'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
                'address' => $customer->address,
                'status' => $customer->status,
                'email_verified' => $customer->email_verified_at !== null,
                'phone_verified' => $customer->phone_verified_at !== null,
                'two_factor_enabled' => $customer->two_factor_enabled,
                'created_at' => $customer->created_at->toISOString(),
            ],
        ];
    }
}
