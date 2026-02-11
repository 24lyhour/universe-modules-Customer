<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Http\Resources\CustomerResource;
use Modules\Customer\Models\Customer;
use Modules\Customer\Services\CustomerService;
use Momentum\Modal\Modal;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search', 'verified']);

        $customers = $this->customerService->paginate(
            perPage: $request->integer('per_page', 10),
            filters: $filters
        );

        return Inertia::render('customer::dashboard/customer/Index', [
            'customers' => CustomerResource::collection($customers)->response()->getData(true),
            'filters' => $filters,
            'stats' => $this->customerService->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Modal
    {
        return Inertia::modal('customer::dashboard/customer/Create')
            ->baseRoute('customer.customers.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->create($request->validated());

        return redirect()->route('customer.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Customer $customer): Response
    {
        // Only load relationships that exist
        $relationships = ['referrer', 'referrals'];

        if (class_exists(\Modules\Outlet\Models\Outlet::class)) {
            $relationships[] = 'outlet';
        }

        if (class_exists(\Modules\Wallets\Models\Wallet::class)) {
            $relationships[] = 'wallet';
        }

        $customer->load($relationships);

        return Inertia::render('customer::dashboard/customer/Show', [
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): Modal
    {
        return Inertia::modal('customer::dashboard/customer/Edit', [
            'customer' => (new CustomerResource($customer))->resolve(),
        ])->baseRoute('customer.customers.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customerService->update($customer, $request->validated());

        return redirect()->route('customer.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);

        return redirect()->route('customer.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
