<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Customer\Models\Customer;
use Modules\Customer\Services\CustomerService;

class CustomerSecurityController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Verify customer email.
     */
    public function verifyEmail(Customer $customer)
    {
        $this->customerService->verifyEmail($customer);

        return back()->with('success', 'Customer email verified successfully.');
    }

    /**
     * Enable two-factor authentication.
     */
    public function enableTwoFactor(Customer $customer)
    {
        $result = $this->customerService->enableTwoFactor($customer);

        return back()->with([
            'success' => 'Two-factor authentication enabled.',
            'recovery_codes' => $result['recovery_codes'],
        ]);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(Customer $customer)
    {
        $this->customerService->disableTwoFactor($customer);

        return back()->with('success', 'Two-factor authentication disabled.');
    }
}
