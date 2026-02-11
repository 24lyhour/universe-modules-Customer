<?php

namespace Modules\Customer\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Http\Resources\CustomerResource;
use Modules\Customer\Models\Customer;
use Modules\Customer\Services\CustomerService;

class CustomerApiController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'search', 'verified', 'outlet_id', 'gender']);

        $customers = $this->customerService->paginate(
            perPage: $request->integer('per_page', 15),
            filters: $filters
        );

        return CustomerResource::collection($customers);
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return response()->json([
            'message' => 'Customer created successfully.',
            'data' => new CustomerResource($customer),
        ], 201);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): CustomerResource
    {
        // Only load relationships that exist
        $relationships = ['referrer'];

        if (class_exists(\Modules\Outlet\Models\Outlet::class)) {
            $relationships[] = 'outlet';
        }

        if (class_exists(\Modules\Wallets\Models\Wallet::class)) {
            $relationships[] = 'wallet';
        }

        $customer->load($relationships);
        $customer->loadCount('referrals');

        return new CustomerResource($customer);
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->customerService->update($customer, $request->validated());

        return response()->json([
            'message' => 'Customer updated successfully.',
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer);

        return response()->json([
            'message' => 'Customer deleted successfully.',
        ]);
    }

    /**
     * Get customer statistics.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->customerService->getStats(),
        ]);
    }

    /**
     * Search customers.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $customers = $this->customerService->search(
            term: $request->string('q'),
            limit: $request->integer('limit', 10)
        );

        return CustomerResource::collection($customers);
    }
}
