<?php

namespace Modules\Customer\Actions\Api;

use Modules\Customer\Models\Customer;

class LogoutAction
{
    /**
     * Execute the logout action.
     */
    public function execute(Customer $customer): array
    {
        $customer->currentAccessToken()->delete();

        return [
            'message' => 'Logged out successfully',
        ];
    }

    /**
     * Logout from all devices.
     */
    public function logoutAll(Customer $customer): array
    {
        $customer->tokens()->delete();

        return [
            'message' => 'Logged out from all devices successfully',
        ];
    }
}
