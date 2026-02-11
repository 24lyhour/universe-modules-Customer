<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Customer\Http\Resources\CustomerResource;
use Modules\Customer\Models\Customer;
use Modules\Customer\Services\CustomerService;
use Momentum\Modal\Modal;

class CustomerStatusController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Show activate confirmation modal.
     */
    public function showActivate(Customer $customer): Modal
    {
        return Inertia::modal('customer::dashboard/customer/Activate', [
            'customer' => (new CustomerResource($customer))->resolve(),
        ])->baseRoute('customer.customers.index');
    }

    /**
     * Activate a customer.
     */
    public function activate(Request $request, Customer $customer)
    {
        $request->validate([
            'confirmed' => ['required', 'accepted'],
        ]);

        $this->customerService->activate($customer);

        return redirect()->route('customer.customers.index')
            ->with('success', 'Customer activated successfully.');
    }

    /**
     * Show deactivate confirmation modal.
     */
    public function showDeactivate(Customer $customer): Modal
    {
        return Inertia::modal('customer::dashboard/customer/Deactivate', [
            'customer' => (new CustomerResource($customer))->resolve(),
        ])->baseRoute('customer.customers.index');
    }

    /**
     * Deactivate a customer.
     */
    public function deactivate(Request $request, Customer $customer)
    {
        $request->validate([
            'confirmed' => ['required', 'accepted'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->customerService->deactivate($customer, $request->reason);

        return redirect()->route('customer.customers.index')
            ->with('success', 'Customer deactivated successfully.');
    }

    /**
     * Show suspend confirmation modal.
     */
    public function showSuspend(Customer $customer): Modal
    {
        return Inertia::modal('customer::dashboard/customer/Suspend', [
            'customer' => (new CustomerResource($customer))->resolve(),
        ])->baseRoute('customer.customers.index');
    }

    /**
     * Suspend a customer.
     */
    public function suspend(Request $request, Customer $customer)
    {
        $request->validate([
            'confirmed' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->customerService->suspend($customer, $request->reason);

        return redirect()->route('customer.customers.index')
            ->with('success', 'Customer suspended successfully.');
    }
}
