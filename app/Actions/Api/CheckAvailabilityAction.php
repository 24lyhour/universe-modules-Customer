<?php

namespace Modules\Customer\Actions\Api;

use Modules\Customer\Models\Customer;

class CheckAvailabilityAction
{
    /**
     * Check if email is available.
     */
    public function checkEmail(string $email): array
    {
        $exists = Customer::where('email', $email)->exists();

        return [
            'available' => !$exists,
            'message' => $exists ? 'Email already taken' : 'Email is available',
        ];
    }

    /**
     * Check if phone is available.
     */
    public function checkPhone(string $phone): array
    {
        $exists = Customer::where('phone', $phone)->exists();

        return [
            'available' => !$exists,
            'message' => $exists ? 'Phone already taken' : 'Phone is available',
        ];
    }
}
